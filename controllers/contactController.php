<?php
// File: controllers/ContactController.php

require_once __DIR__ . '/../models/Contact.php';

class ContactController {
    private $model;
    
    public function __construct() {
        $this->model = new ContactModel();
    }
    
    public function handleRequest() {
        $success = false;
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeInput($_POST);
            $errors = $this->validateInput($data);
            
            if (empty($errors)) {
                $success = $this->model->saveContact($data);
                
                if ($success) {
                    header('Location: ' . $_SERVER['REQUEST_URI'] . '?success=1');
                    exit;
                } else {
                    $errors[] = "Gagal menyimpan pesan. Silakan coba lagi.";
                }
            }
        }
        
        // Tampilkan view
        require __DIR__ . '/../views/kontak/index.php';
    }
    
    private function sanitizeInput($input) {
        return [
            'name' => trim(htmlspecialchars($input['name'] ?? '')),
            'telephone' => trim(htmlspecialchars($input['telephone'] ?? '')),
            'email' => trim(htmlspecialchars($input['email'] ?? '')),
            'purpose' => trim(htmlspecialchars($input['purpose'] ?? '')),
            'message' => trim(htmlspecialchars($input['message'] ?? ''))
        ];
    }
    
    private function validateInput($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = "Nama harus diisi";
        }
        
        if (empty($data['telephone'])) {
            $errors['telephone'] = "Telepon harus diisi";
        } elseif (!preg_match('/^[0-9]{10,13}$/', $data['telephone'])) {
            $errors['telephone'] = "Format telepon tidak valid";
        }
        
        if (empty($data['email'])) {
            $errors['email'] = "Email harus diisi";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Format email tidak valid";
        }
        
        if (empty($data['message'])) {
            $errors['message'] = "Pesan harus diisi";
        } elseif (strlen($data['message']) < 10000) {
            $errors['message'] = "Pesan terlalu pendek";
        }
        
        return $errors;
    }
}