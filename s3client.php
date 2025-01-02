<?php
    //Load Composer's autoloader
   require __DIR__ . '/../vendor/autoload.php';//__DIR__ is a magic constant in PHP that returns the absolute parent directory path of the current script(means the path of the parent folder of the current file).

   use Aws\S3\S3Client;
   use Aws\Exception\AwsException;

   // Initialize Dotenv and Load environment variables from .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'cred.env');
    $dotenv->load();  

    // Initialize connection
    $s3Client = new Aws\S3\S3Client([
        "credentials" => [
            "key" => $_ENV['ACCESS_KEY'],
            "secret" => $_ENV['SECRET_KEY']
        ],
        "endpoint" => "https://s3.tebi.io",
        "region" => "sg",
        "version" => "2006-03-01"
    ]);

    $TEBI_BUCKET = $_ENV['BUCKET']; 

    // Initialize connection for the second bucket
    $s3Client2 = new Aws\S3\S3Client([
        "credentials" => [
            "key" => $_ENV['ACCESS_KEY2'],
            "secret" => $_ENV['SECRET_KEY2']
        ],
        "endpoint" => "https://s3.tebi.io",
        "region" => "sg",
        "version" => "2006-03-01"
    ]);

    $TEBI_BUCKET2 = $_ENV['BUCKET2']; 
?>