<?php
$page = $_GET['page'] ?? 'menu';

switch ($page) {
    case 'boxcatering':
        require_once 'controllers/boxcateringController.php';
        $controller = new boxcateringController();
        $controller->index();
        break;

        case 'kontak':
            require 'controllers/contactController.php';
            $controller = new ContactController();
            $controller->handleRequest();
            break;

        case 'lokasi':
            require 'controllers/LokasiController.php';
            $controller = new LokasiController();
            $controller->showBranches();
            break;
        
        case 'project':
            require 'controllers/HomeController.php';
            $controller = new HomeController();
            $controller->index();
            break;

        case 'menu':
            require 'controllers/MenuController.php';
            $controller = new MenuController();
            $controller->showMenu();
            break;
        

        

    // Tambahan lain bisa ditaruh di sini...

    default:
        echo 'Halaman tidak ditemukan.';
        break;
}