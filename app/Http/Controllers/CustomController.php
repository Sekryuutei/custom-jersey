<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Custom;

class CustomController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function store(Request $request)
    {
        $cdrData = $request->cdrData;

        // Convert SVG to CDR (requires external tool like Inkscape or library)
        $cdrFileName = 'design_' . time() . '.cdr';
        $cdrFilePath = storage_path('app/public/' . $cdrFileName);

        file_put_contents($cdrFilePath, $cdrData);

        // Save file info to database
        $custom = new Custom();
        $custom->file_name = $cdrFileName;
        $custom->file_path = 'public/' . $cdrFileName;
        $custom->save();

        return response()->json(['message' => 'Design saved successfully!']);
    }

    public function download($id)
    {
        $custom = Custom::findOrFail($id);
        return response()->download(storage_path('app/' . $custom->file_path));
    }

}

