<?php

namespace App\Services;

class SEOManagementService
{
    public function generateMeta(array $data)
    {
        return [
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ];
    }
}
