<?php
// File: PROJECR2/index.php

// ============================================
// MULAI SESSION DI AWAL (WAJIB PALING ATAS)
// ============================================
session_start();

// ============================================
// AUTOLOADER (Otomatis load file class)
// ============================================
spl_autoload_register(function ($class_name) {
    // List direktori tempat menyimpan class (Controllers, Models, dll.)
    $directories = [
        'controllers/',
        'models/',
        // Tambahkan direktori lain jika perlu, misal 'includes/'
    ];

    foreach ($directories as $dir) {
        $file = __DIR__ . '/' . $dir . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return; // Hentikan jika file ditemukan
        }
    }
});

// ============================================
// TENTUKAN HALAMAN YANG DIMINTA
// ============================================
// Default ke 'menu' jika parameter ?page tidak ada atau kosong
// Sesuaikan 'menu' jika halaman default Anda berbeda (misal 'project' atau 'home')
$page = isset($_GET['page']) ? trim($_GET['page']) : 'menu';

// ============================================
// ROUTING UTAMA
// ============================================
switch ($page) {

    case 'kontak':
         // Nama class biasanya PascalCase: ContactController
        if (class_exists('ContactController')) {
            $controller = new ContactController();
            // Ganti 'handleRequest' jika methodnya berbeda
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
         // Nama class biasanya PascalCase: LokasiController
        if (class_exists('LokasiController')) {
            $controller = new LokasiController();
             // Ganti 'showBranches' jika methodnya berbeda
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

    case 'project': // Anda punya case ini, mungkin halaman utama?
        // Nama class biasanya PascalCase: HomeController
        if (class_exists('HomeController')) {
            $controller = new HomeController();
             // Ganti 'index' jika methodnya berbeda
             if (method_exists($controller, 'index')) {
                $controller->index();
            } else {
                echo 'Error: Method index tidak ditemukan di HomeController.';
                 http_response_code(500);
            }
        } else {
             // Opsi jika HomeController tidak ada: tampilkan pesan default atau error 404
             // echo '<h1>Selamat Datang di Project!</h1>'; // Pesan default
             echo 'Error: Controller HomeController tidak ditemukan.'; // Error 404
             http_response_code(404);
        }
        break;

    case 'menu': // Case yang sedang kita fokuskan
        // Nama class: MenuController
        if (class_exists('MenuController')) {
            $controller = new MenuController();
            if (method_exists($controller, 'showMenu')) {
                $controller->showMenu(); // Ini akan memuat model dan view menu
            } else {
                 echo 'Error: Method showMenu tidak ditemukan di MenuController.';
                  http_response_code(500);
            }
        } else {
            echo 'Error: Controller MenuController tidak ditemukan.';
            http_response_code(404);
        }
        break;

    // Tambahkan case lain jika ada

    default: // Jika nilai ?page tidak cocok dengan case di atas
        http_response_code(404); // Set status Not Found
        echo '<h1>404 - Halaman Tidak Ditemukan</h1>';
        // Anda bisa include file view khusus untuk 404 di sini
        // include 'views/errors/404.php';
        break;
} // Akhir Switch

?>