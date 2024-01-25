<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se recibió la pregunta del chat
    if (isset($_POST['mensaje'])) {
        // Obtener la pregunta del chat
        $pregunta = $_POST['mensaje'];
        // Obtener palabras clave desde la base de datos
        $palabrasClave = obtenerPalabrasClaveDesdeBD();
        // Verificar si la pregunta contiene información relacionada con computadoras
        if (contienePalabrasClave($pregunta, $palabrasClave)) {
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
                    $respuesta .= "<br><br>¡Buenas noticias! Tenemos los siguientes productos recomendados en nuestros productos a la venta:<br>";
                    $respuesta .= "<ul>";

                    foreach ($infoProductos as $infoProducto) {
                        $respuesta .= "<li>$infoProducto</li>";
                    }

                    $respuesta .= "</ul>";
                } else {
                    $respuesta .= "\nLo siento, actualmente no tenemos productos disponibles que coincidan con la recomendación antes dada.";
                }
            } else {
                // Manejar el caso en que la respuesta del modelo no contiene contenido
                $respuesta = "Lo siento, no pude entender tu pregunta.";
            }

            curl_close($ch);

            echo $respuesta;
        } else {
            // Respuesta cuando la pregunta tiene información relacionada con computadoras
            echo "Como experto en computadoras, no puedo ayudarte con esa pregunta. ¿Tienes alguna otra pregunta relacionada con las computadoras?";
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
    $sql = "SELECT Nombre, Precio FROM productos WHERE Stock > 0";
    $result = $conn->query($sql);

    $productosRecomendados = [];

    if ($result !== false && $result->num_rows > 0) {
        while ($producto = $result->fetch_assoc()) {
            // Utilizamos stripos para hacer la búsqueda insensible a mayúsculas y minúsculas
            // Verificamos si el nombre del producto está presente en la respuesta
            if (stripos($respuesta, $producto['Nombre']) !== false) {
                $productosRecomendados[] = $producto['Nombre'] . ", su precio es: $" . $producto['Precio'];
            }
        }
    } 
    // Retornar la lista de productos recomendados
    return $productosRecomendados;
}



// Función para obtener palabras clave desde la base de datos
function obtenerPalabrasClaveDesdeBD() {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_password = '';
    $db_name = 'integrador_sexto';

    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    $palabrasClave = [];
    $sql = "SELECT palabras FROM palabritas";
    $result = $conn->query($sql);

    if ($result !== false && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $palabrasClave[] = $row['palabras'];
        }
    }

    return $palabrasClave;
}

// Función para verificar si la pregunta contiene palabras clave
function contienePalabrasClave($pregunta, $palabrasClave) {
    foreach ($palabrasClave as $palabra) {
        if (stripos($pregunta, $palabra) !== false) {
            return true;
        }
    }

    return false;
}
?>
