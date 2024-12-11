<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;

class LogViewer extends Field
{

    public $logContent;

    public function mount()
    {
        $this->streamLogContent();
    }

    public function streamLogContent()
    {
        $filePath = 'log/1.txt';

        /* if (Storage::exists($filePath)) {
            $handle = Storage::readStream($filePath);
            while (!feof($handle)) {
                $line = fgets($handle);
                $this->logContent .= $line;
                $this->stream(to: 'logContent', content: $this->logContent, replace: true);
                // usleep(100000); // Sleep for 0.1 seconds to simulate streaming
            }
            fclose($handle);
        } else {
            $this->logContent = 'Log file not found.';
        } */
    }

   
    protected string $view = 'forms.components.log-viewer';
}
