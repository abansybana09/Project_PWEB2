<?php
// File: controllers/HomeController.php

require_once __DIR__ . '/../models/Project.php';

class HomeController {
    private $model;

    public function __construct() {
        $this->model = new HomeModel();
    }

    public function index() {
        // Menggabungkan semua data yang dibutuhkan view
        $data = [
            'features' => $this->model->getFeatures(),
            'location' => $this->model->getLocationInfo(),
            'hero' => [
                'title' => 'Warung Mang Oman',
                'subtitle' => 'Ayam Enak Harga Murah',
                'image' => 'img/bakar.jpg'
            ]
        ];

        // Load view
        require_once __DIR__ . '/../views/project/index.php';
    }
}