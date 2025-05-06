<?php
session_start();

spl_autoload_register(function ($class_name) {
    $directories = [
        'controllers/',
        'models/',
    ];

    foreach ($directories as $dir) {
        $file = __DIR__ . '/' . $dir . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$page = isset($_GET['page']) ? trim($_GET['page']) : 'menu';

switch ($page) {

    case 'kontak':
        if (class_exists('ContactController')) {
            $controller = new ContactController();
            if (method_exists($controller, 'handleRequest')) {
                $controller->handleRequest();
            } else {
                echo 'Error: Method handleRequest tidak ditemukan di ContactController.';
                http_response_code(500);
            }
        } else {
            echo 'Error: Controller ContactController tidak ditemukan.';
            http_response_code(404);
        }
        break;

    case 'lokasi':
        if (class_exists('LokasiController')) {
            $controller = new LokasiController();
            if (method_exists($controller, 'showBranches')) {
                $controller->showBranches();
            } else {
                echo 'Error: Method showBranches tidak ditemukan di LokasiController.';
                http_response_code(500);
            }
        } else {
            echo 'Error: Controller LokasiController tidak ditemukan.';
            http_response_code(404);
        }
        break;

    case 'project':
        if (class_exists('HomeController')) {
            $controller = new HomeController();
            if (method_exists($controller, 'index')) {
                $controller->index();
            } else {
                echo 'Error: Method index tidak ditemukan di HomeController.';
                http_response_code(500);
            }
        } else {
            echo 'Error: Controller HomeController tidak ditemukan.';
            http_response_code(404);
        }
        break;

    case 'menu':
        if (class_exists('MenuController')) {
            $controller = new MenuController();
            if (method_exists($controller, 'showMenu')) {
                $controller->showMenu();
            } else {
                echo 'Error: Method showMenu tidak ditemukan di MenuController.';
                http_response_code(500);
            }
        } else {
            echo 'Error: Controller MenuController tidak ditemukan.';
            http_response_code(404);
        }
        break;

    default:
        http_response_code(404);
        echo '<h1>404 - Halaman Tidak Ditemukan</h1>';
        break;
}
?>
