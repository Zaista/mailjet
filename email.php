<?php

    require 'vendor/autoload.php';
    use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
    use \Mailjet\Resources;

    if (empty($_ENV['GAE_ENV'])) {
        // local environment
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $apikey = $_ENV['APIKEY'];
        $apisecret = $_ENV['APISECRET'];
    } else {
        // production environment
        $projectId = 'deductive-span-313911';
        $versionId = 'latest';
        
        // Create the Secret Manager client.
        $client = new SecretManagerServiceClient();
    
        // Build the resource name of the secret version.
        $name = $client->secretVersionName($projectId, 'mail-apiskey', $versionId);
        $secret = $client->accessSecretVersion($name);
        $apikey = $secret->getPayload()->getData();

        $name = $client->secretVersionName($projectId, 'mail-apisecret', $versionId);
        $secret = $client->accessSecretVersion($name);
        $apisecret = $secret->getPayload()->getData();
    }

    $mail_server = new \Mailjet\Client($apikey, $apisecret, true, ['version' => 'v3.1']);
    
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $response = $mail_server->post(Resources::$Email, ['body' => $data]);
    $response->success() && var_dump($response->getData());
