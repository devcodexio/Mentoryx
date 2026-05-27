<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Setting;

class SettingController {
    protected $settingModel;

    public function __construct() {
        $this->settingModel = new Setting();
    }

    public function index(Request $request, Response $response) {
        $settings = $this->settingModel->all();
        $response->render('admin/settings/index', [
            'settings' => $settings,
            'title' => 'Configuración de la Página'
        ]);
    }

    public function update(Request $request, Response $response) {
        if ($request->getMethod() === 'POST') {
            $data = $request->all();
            
            // Handle logo upload
            if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['logo_file'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                    $fileName = 'logo_' . uniqid() . '.' . $ext;
                    $uploadPath = dirname(dirname(__DIR__)) . '/public/uploads/' . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $data['logo'] = $fileName;
                    }
                } else {
                    Session::setFlash('error', 'El logo debe ser imagen (JPG/PNG/GIF/SVG) y máximo 2MB.');
                    $response->redirect('/admin/configuracion');
                    return;
                }
            }

            // Remove csrf_token from data before saving
            unset($data['csrf_token']);
            
            foreach ($data as $key => $value) {
                // If logo was not uploaded but they checked "remove logo", handle it here.
                // We'll trust the form fields for now.
                $this->settingModel->update($key, trim($value));
            }

            Session::setFlash('success', 'Configuración actualizada correctamente.');
            $response->redirect('/admin/configuracion');
        }
    }
}
