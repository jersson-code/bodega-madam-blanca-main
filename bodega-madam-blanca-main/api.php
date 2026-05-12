<?php
// Archivo: api.php

// Cabeceras para permitir solicitudes desde cualquier origen
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Carga de datos simulados
$constructoras = [
    ["id" => 1, "nombre" => "Constructora Bolívar", "ciudad" => "Bogotá", "ranking" => 1],
    ["id" => 2, "nombre" => "Marval", "ciudad" => "Bucaramanga", "ranking" => 2],
    ["id" => 3, "nombre" => "Arquitectura & Concreto", "ciudad" => "Medellín", "ranking" => 3],
    ["id" => 4, "nombre" => "Colpatria", "ciudad" => "Bogotá", "ranking" => 4],
    ["id" => 5, "nombre" => "Coninsa Ramón H.", "ciudad" => "Medellín", "ranking" => 5],
    ["id" => 6, "nombre" => "Conino  C.", "ciudad" => "Choco", "ranking" => 3],
];

// Obtén el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// Procesa las solicitudes
if ($method === 'GET') {
    // Verifica si hay un parámetro de búsqueda
    if (isset($_GET['ciudad'])) {
        $ciudad = strtolower(trim($_GET['ciudad']));
        $resultado = array_filter($constructoras, function ($c) use ($ciudad) {
            return strtolower($c['ciudad']) === $ciudad;
        });
        echo json_encode(["success" => true, "data" => array_values($resultado)]);
    } else {
        // Devuelve todas las constructoras
        echo json_encode(["success" => true, "data" => $constructoras]);
    }
} else {
    // Método no soportado
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
