<?php
// File: models/ContactModel.php

class ContactModel {
    private $logFile = __DIR__ . '/../../contact_log.txt';
    
    public function saveContact($data) {
        // Format data untuk disimpan
        $logData = [
            'Waktu' => date('Y-m-d H:i:s'),
            'Nama' => $data['name'],
            'Telepon' => $data['telephone'],
            'Email' => $data['email'],
            'Tujuan' => $data['purpose'],
            'Pesan' => str_replace("\n", " ", $data['message'])
        ];
        
        // Konversi ke format JSON
        $logEntry = json_encode($logData, JSON_PRETTY_PRINT) . "\n---\n";
        
        // Simpan ke file
        return file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    public function getAllContacts() {
        if (file_exists($this->logFile)) {
            $content = file_get_contents($this->logFile);
            $entries = explode("---\n", $content);
            return array_filter($entries);
        }
        return [];
    }
}