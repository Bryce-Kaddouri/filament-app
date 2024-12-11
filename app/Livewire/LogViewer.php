<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

use Illuminate\Support\Facades\Storage;

class LogViewer extends Component
{
   /*  public function mount()
    {
        $this->begin();

    } */
   

    public $start = 3;
    public $logContent;


    public function streamLogContent()
    {
        $filePath = 'log/1.txt';
        $lastModifiedTime = request()->query('lastModifiedTime', 0);
        $startTime = microtime(true);

        if (Storage::exists($filePath)) {
            $currentModifiedTime = null;

            while (($currentModifiedTime = Storage::lastModified($filePath)) < $lastModifiedTime) {
                usleep(1000); // Sleep for 1 millisecond

                // If more than 1 minute has passed
                if ((microtime(true) - $startTime) > 60) {
                    return response()->json(['status' => 'not_modified'], 304);
                }
            }

            $data = Storage::get($filePath);
            

            $this->logContent = $data;
        } else {
            $this->logContent = 'Log file not found.';
        }
    }
 
    public function begin()
    {
        while ($this->start >= 0) {
            // Stream the current count to the browser...
            $this->stream(  
                to: 'count',
                content: $this->start,
                replace: true,
            );
 
            // Pause for 1 second between numbers...
            sleep(1);
 
            // Decrement the counter...
            $this->start = $this->start - 1;
        };
    }
 
    public function render()
    {
        return view('livewire.log-viewer');
    }
}