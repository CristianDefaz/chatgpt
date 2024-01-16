<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

            if ($response === false) {
                die(curl_error($ch));
            }

            $decoded_response = json_decode($response, true);

            if (isset($decoded_response['choices'][0]['message']['content'])) {
                $respuesta = $decoded_response['choices'][0]['message']['content'];

                // Buscar productos recomendados en la base de datos y obtener información adicional
                $infoProductos = buscarProductosEnRespuesta($respuesta);

                if (!empty($infoProductos)) {
                    $respuesta .= "<br><br>¡Buenas noticias! Tenemos los siguientes productos recomendados en venta:<br>";
                    $respuesta .= "<ul>";

                    foreach ($infoProductos as $infoProducto) {
                        $respuesta .= "<li>$infoProducto</li>";
                    }

                    $respuesta .= "</ul>";
                } else {
                    $respuesta .= "\nLo siento, actualmente no tenemos productos disponibles que coincidan con la recomendación.";
                }
            } else {
                // Manejar el caso en que la respuesta del modelo no contiene contenido
                $respuesta = "Lo siento, no pude entender tu pregunta.";
            }

            curl_close($ch);

            echo $respuesta;
        } else {
            // Respuesta cuando la pregunta tiene información relacionada con computadoras
            echo "Como experto en computadoras, no puedo ayudarte con eso. ¿Tienes alguna otra pregunta relacionada con las computadoras?";
        }
    }
}

// Función para buscar información de productos en la base de datos
function buscarProductosEnRespuesta($respuesta) {
    // Conectar a la base de datos (actualiza con tus credenciales)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_password = '';
    $db_name = 'integrador_sexto';

    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    // Verificar la conexión a la base de datos
    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    // Realizar una búsqueda en la base de datos para encontrar productos por nombre
    $sql = "SELECT Nombre, Precio FROM productos WHERE Stock>0";
    $result = $conn->query($sql);

    $productosRecomendados = [];

    if ($result !== false && $result->num_rows > 0) {
        while ($producto = $result->fetch_assoc()) {
            // Verificar si el nombre del producto está presente en la respuesta
            if (stripos($respuesta, $producto['Nombre']) !== false) {
                $productosRecomendados[] = $producto['Nombre'] . " - $" . $producto['Precio'];
            }
        }
    }

    // Retornar la lista de productos recomendados
    return $productosRecomendados;
}
?>
