<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Template;

class TemplateController extends Controller
{
    public function home()
    {
        return view('index');
    }
    public function index()
    {
        // Menggunakan latest() untuk memastikan template terbaru muncul pertama.
        $templates = Template::latest()->get();
        return view('templates.index', compact('templates'));
    }

    public function guide()
    {
        return view('guide.index');
    }

    /**
     * Menampilkan halaman desain untuk template yang dipilih.
     */
    public function showDesign(Template $template)
    {
        // Anda perlu membuat view ini: resources/views/design.blade.php
        return view('design.index', compact('template'));
    }
}
