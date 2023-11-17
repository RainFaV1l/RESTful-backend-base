<?php

// Выдаем разрешение на обращение к API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Credentials: true');

// Показываем контент на сайте с типом application/json
header('Content-type: application/json');

require 'connect.php';

global $connect;

$method = $_SERVER['REQUEST_METHOD'];

if(!isset($_GET['q'])) die('Get request not defined!');

$q = $_GET['q'];

$params = explode('/', $q);

if(isset($params[0])) $type = $params[0];

if(isset($params[1])) $id = $params[1];

if($method === 'POST' && isset($_POST) && isset($type) && $type === 'posts') {

    $title = $_POST['title'];

    $body = $_POST['body'];

    $sql = "INSERT INTO `posts` (`id`, `title`, `body`) VALUES (NULL, '$title', '$body')";

    $connect->query($sql);

    http_response_code(201);

    $response = [
        'status' => true,
        'post_id' => $connect->lastInsertId()
    ];

    // Преобразуем массив в json с помощью функции json_encode
    echo json_encode($response);

    die();
}

if($method === 'PATCH' && isset($type) && $type === 'posts' && isset($id)) {

    // Получаем данный в виде json
    $data = file_get_contents('php://input');

    // Преобразуем полученные данные в массив (объект) с помощью функции json_decode
    $data = json_decode($data);

    // Можно получить обычный ассоциативный массив
    // $data = json_decode($data, true);

    $sql = "UPDATE `posts` SET `title` = '$data->title', `body` = '$data->body' WHERE `posts`.`id` = $id";

    $connect->query($sql);

    http_response_code(200);

    $response = [
        'status' => true,
        'message' => 'Post is updated'
    ];

    echo json_encode($response);

    die();

}

if($method === 'DELETE' && isset($type) && $type === 'posts' && isset($id)) {

    $sql = "DELETE FROM posts WHERE `posts`.`id` = $id";

    $connect->query($sql);

    http_response_code(200);

    $response = [
        'status' => true,
        'message' => 'Post is deleted'
    ];

    echo json_encode($response);

    die();
}

if($type === 'posts') {

    if(isset($id)) {

        $posts = $connect->query("SELECT * FROM `posts` WHERE id = $id")->fetch(PDO::FETCH_ASSOC);

        if(empty($posts)) {

            $response = [
                'status' => false,
                'message' => 'Post not found'
            ];

            echo json_encode($response);;

            http_response_code(404);

            die();

        }

        echo json_encode($posts);

        die();

    }

    $posts = $connect->query('SELECT * FROM `posts`')->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($posts);

}