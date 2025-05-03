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
        
        // Hapus modifikasi link WhatsApp karena akan dihandle oleh JavaScript
        require_once __DIR__ . '/../views/menu/index.php';
    }
}