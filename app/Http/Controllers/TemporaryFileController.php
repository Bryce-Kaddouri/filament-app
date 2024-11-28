<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemporaryFileController extends Controller
{
    public function serve(Request $request, $filename)
    {
        // dd($request->isPrivate, $filename);
        $fullpath = '';
        $fileExists = false;

        if($request->isPrivate){
            $fullpath = 'app/private/livewire-tmp/' . $filename;

            $filePath = 'livewire-tmp/' . $filename;
            $fileExists = Storage::disk('local')->exists($filePath);
           

        }else{
            $fullpath = 'app/public/bills/' . $filename;
            $fileExists = Storage::disk( 'public')->exists('bills/' . $filename);


        }

        if($fileExists){
            return response()->file(storage_path($fullpath));
        }
        abort(404, "File not found.");

        // dd($fullpath);
        
           
       

        if (Storage::disk('local')->exists($fullpath)) {
            //dd(storage_path('app/private/' . $path));
            return response()->file(storage_path($fullpath));
        }
        abort(404, "File not found.");
    }
}
