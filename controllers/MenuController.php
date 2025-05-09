<?php
// File: controllers/MenuController.php

// Tidak perlu require_once model jika sudah pakai autoloader di index.php
// require_once __DIR__ . '/../models/Menu.php';

class MenuController
{
    private $model;

    public function __construct()
    {
        // Pastikan class MenuModel ada (jika tidak pakai autoloader, require_once di atas)
        if (!class_exists('MenuModel')) {
            die("Fatal Error: Class MenuModel tidak ditemukan.");
        }
        $this->model = new MenuModel();
    }

    public function showMenu()
    {
        // Panggil model untuk mendapatkan data
        $data = $this->model->getAllMenuItems();
        $viewPath = __DIR__ . '/../views/menu/index.php';

        if (file_exists($viewPath)) {
            // Variabel $data akan tersedia di dalam file view yang di-include
            include $viewPath;
        } else {
            echo "<div class='alert alert-danger'>Error Controller: File view tidak ditemukan di $viewPath</div>";
        }
    }
}
