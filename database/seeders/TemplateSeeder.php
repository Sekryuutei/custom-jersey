<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    public function run()
    {
        Template::create([
            'name' => 'Coqpink',
            'image_path' => 'templates/coqpink.svg'
        ]);
        Template::create([
            'name' => 'Coqblue',
            'image_path' => 'templates/coqblue.svg'
        ]);
        Template::create([
            'name' => 'Coqballet',
            'image_path' => 'templates/coqballet.svg'
        ]);
    }
}
