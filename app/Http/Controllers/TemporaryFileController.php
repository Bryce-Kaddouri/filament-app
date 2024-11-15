<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemporaryFileController extends Controller
{
    public function serve(Request $request, $filename)
    {
        $path = 'livewire-tmp/' . $filename;
/*         dd(vars: storage_path('app/private/livewire-tmp/'));
 */    /*     dd(Storage::temporaryUrl(
    'storage/app/private/livewire-tmp/AFWqzajpbja04yCF13loTo2JhehYn5-metaSW52b2ljZS1DVkRKTE8tMDAwMDMucGRm-.pdf', now()->addMinutes(5)
)); */
$files = Storage::disk('local')->files('livewire-tmp/');
/* dd($files);
dd(response()->file(storage_path('app/' . $path))); */

        
           
       

        if (Storage::disk('local')->exists($path)) {
            //dd(storage_path('app/private/' . $path));
            return response()->file(storage_path('app/private/' . $path));
        }
        abort(404, "File not found.");
    }
}
