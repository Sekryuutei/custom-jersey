<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    public function run()
    {
        Template::create([
            'file_name' => 'Template 1',
            'image_path' => 'templates/templatemerah.svg',
        ]);

        Template::create([
            'file_name' => 'Template 2',
            'image_path' => 'templates/templatebiru.svg'
        ]);
    }
}
