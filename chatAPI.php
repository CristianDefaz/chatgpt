<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se recibió la pregunta del chat
    if (isset($_POST['mensaje'])) {
        // Obtener la pregunta del chat
        $pregunta = $_POST['mensaje'];

        // Verificar si la pregunta contiene información relacionada con computadoras
        if (stripos($pregunta, 'computadora') !== false || stripos($pregunta, 'computador') !== false || stripos($pregunta, 'memoria ram') !== false || stripos($pregunta, 'disco duro') !== false) {
            $api_key = "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";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key,
            ]);

            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [],
            ];

            $data['messages'][] = ['role' => 'system', 'content' => 'Actua como un experto '];
            $data['messages'][] = ['role' => 'user', 'content' => $pregunta];

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $respuesta = '';
            $decoded_response = json_decode($response, true);

            if (isset($decoded_response['choices'][0]['message']['content'])) {
                $respuesta = $decoded_response['choices'][0]['message']['content'];
            }

            curl_close($ch);

            echo $respuesta;
        } else {
            // Respuesta cuando la pregunta tiene información relacionada con computadoras
            echo "Como experto en computadoras, no puedo ayudarte con eso. ¿Tienes alguna otra pregunta relacionada con las computadoras?";
        }
    }
}
?>
