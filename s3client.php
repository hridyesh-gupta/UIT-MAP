<?php
    //Load Composer's autoloader
   require __DIR__ . '/vendor/autoload.php';//__DIR__ is a magic constant in PHP that returns the absolute directory path of the current script(means the current path of the file).

   use Dotenv\Dotenv;
   use Aws\S3\S3Client;
   use Aws\Exception\AwsException;

    //Load environment variables from .env file
   $dotenv = Dotenv::createImmutable(__DIR__, 'cred.env');
   $dotenv->load();

   $accessKey = $_ENV['ACCESS_KEY'];
   $secretKey = $_ENV['SECRET_KEY'];
   $bucket = $_ENV['BUCKET'];
   $region = $_ENV['REGION'];
   $endpoint = $_ENV['ENDPOINT'];

   //Create S3 client
   $s3Client = new S3Client([
    'credentials' => [
        'key' => $accessKey,
        'secret' => $secretKey,
    ],
    'endpoint' => $endpoint,
    'region' => $region,
    'version' => 'latest',
    'use_path_style_endpoint' => true,
   ]);