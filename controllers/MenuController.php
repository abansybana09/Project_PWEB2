<?php
// File: controllers/MenuController.php

require_once __DIR__ . '/../models/Menu.php';

class MenuController {
    private $model;

    public function __construct() {
        $this->model = new MenuModel();
    }

    public function showMenu() {
        $data = $this->model->getAllMenuItems();
        
        // Tambahkan parameter quantity ke link WhatsApp
        foreach ($data as &$item) {
            $item['whatsapp_link'] .= '%20(%quantity%20bungkus)';
        }
        
        require_once __DIR__ . '/../views/menu/index.php';
    }
}