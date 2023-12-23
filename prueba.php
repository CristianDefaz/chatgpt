<?php

$api_key = "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";
$pregunta = "";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"model\": \"gpt-3.5-turbo\",\n    \"messages\": [\n      {\n        \"role\": \"system\",\n        \"content\": \"You are a helpful assistant.\"\n      },\n      {\n        \"role\": \"user\",\n        \"content\": \".$pregunta .\"\n      }\n    ]\n  }");

$response = curl_exec($ch);

        $respuesta = json_decode($response);
        var_dump($respuesta);
        //$decoded_response = json_decode($response, true);


curl_close($ch);