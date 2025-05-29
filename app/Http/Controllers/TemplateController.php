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
        $templates = Template::all();
        return view('templates.index', compact('templates'));
    }

    public function design($templateId)
    {
        $template = Template::findOrFail($templateId);
        return view('design', compact('template'));
    }
}
