<?php

require 'vendor/autoload.php';

try {
    $service = new \App\Service\ChatbotService();
    $response = $service->generateResponse('bonjour');
    echo "Success: " . $response . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
