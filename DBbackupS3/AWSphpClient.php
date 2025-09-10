<?php
// Require the Composer autoloader.
require 'aws/aws-autoloader.php';
include(dirname(__FILE__)."/php/Logger.php");
Logger::configure(dirname(__FILE__)."/php/config.xml");
use Aws\S3\S3Client;

// Instantiate an Amazon S3 client.
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1',
	'credentials' => [
        'key'    => 'awsapikey',
        'secret' => 'secretkey',
    ],
]);
// Upload a publicly accessible file. The file size and type are determined by the SDK.

$local_folder = '/external_storage/Rammohan/db_backups/facelogin';
$bucket_name = 'facelogindb-backup';
$logger = Logger::getLogger("facelogin db s3 upload : ");


$parentFolder=getLastItemAfterSplit($local_folder,'/');
nestedfiles($bucket_name,$local_folder,$parentFolder);
getObjectsFromS3BucketandDelete($bucket_name);


function getLastItemAfterSplit($splitstring,$splitsymbol){
	$parentFolderPath=explode($splitsymbol,$splitstring);
	$parentFolder = $parentFolderPath[count($parentFolderPath)-1];
	return $parentFolder;
}

function nestedfiles($bucket_name,$local_folder,$parentFolder){
	$subdir = scandir($local_folder);
	foreach ($subdir as $key => $value) {
	      if (!in_array($value,array(".","..")))
	      {
		if(is_dir($local_folder.'/'.$value))
		{
			//var_dump(" Dir -- ".$parentFolder.'/'.$value);
			nestedfiles($bucket_name,$local_folder.'/'.$value,$parentFolder.'/'.$value);
		}
		else
		{
			upload_file($local_folder.'/'.$value,$bucket_name,$parentFolder.'/'.$value);
			//var_dump(" file -- ".$parentFolder.'/'.$value);
			//var_dump(" local path -- ".$local_folder.'/'.$value);
		}
	      }
	}
}


function upload_file($local_folder,$bucket_name,$file_name){
	global $s3,$logger;

	try {
		// Check if the file has a .zip extension before uploading
		if (pathinfo($file_name, PATHINFO_EXTENSION) === 'zip') {

			$s3->putObject([
			'Bucket' => $bucket_name,
			'Key'    => $file_name,
			'Body'   => fopen($local_folder, 'r'),
			]);
			echo $file_name." file is uploaded to s3\n";
			$logger->info($file_name." file is uploaded to s3\n");
		} else {
            echo $file_name . " is not a .zip file and will not be uploaded to s3\n";
        }
		
	} catch (Aws\S3\Exception\S3Exception $e) {
	    echo "There was an error uploading the file.\n".$e;
		$logger->info("There was an error uploading the file.\n".$e);
	    return;
	}

}

function getObjectsFromS3BucketandDelete($bucket){
	global $s3,$logger;
	// Use the high-level iterators (returns ALL of your objects).
	try {
	    $results = $s3->getPaginator('ListObjects', [
		'Bucket' => $bucket
	    ]);

	    foreach ($results as $result) {
		foreach ($result['Contents'] as $object) {
		    $filename=getLastItemAfterSplit($object['Key'],'/');
		    preg_match_all('/\d{2}\-\d{2}\-\d{4}/',$filename,$matches);
		    if(count($matches[0])){
			if(strtotime($matches[0][0]) < strtotime('-30 day')){    // it's been longer than 30 days
	    $logger->info('matches the file to delete ' . $matches[0][0]." => ".(strtotime($matches[0][0]) < strtotime('-30 day')));
				deleteObjectFromS3($bucket,$object['Key']);
			}
		    }
		}//end of inner foreach
	    }//end of outer foreach
	} catch (S3Exception $e) {
	    echo $e->getMessage() . PHP_EOL;
	}
}

function deleteObjectFromS3($bucket,$keyname){
	global $s3,$logger;
	// 1. Delete the object from the bucket.
	try
	{
	    echo 'Attempting to delete ' . $keyname . '...' . PHP_EOL;
	    $logger->info('Attempting to delete ' . $keyname);
	    $result = $s3->deleteObject([
		'Bucket' => $bucket,
		'Key'    => $keyname
	    ]);
	}
	catch (S3Exception $e) {
	    $logger->info('Error: ' . $e->getAwsErrorMessage());
	    exit('Error: ' . $e->getAwsErrorMessage() . PHP_EOL);
	}
}
?>
