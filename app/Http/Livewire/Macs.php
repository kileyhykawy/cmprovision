<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Mac;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use \Illuminate\Http\UploadedFile;

class Macs extends Component
{
    use \Livewire\WithFileUploads;

    public $MACs, $maxfilesize, $freediskspace;
    public $isImportOpen = false;
    public $mac;
    public $projectId = -1;
    public $projects;


    public function render()
    {
        if ($this->projectId == -1)
        {
            $this->projectId = Project::getActiveId();
        }

        if ($this->projectId)
        {
            $this->MACs = Mac::where('project_id', $this->projectId)->orderBy('mac')->get();
        }
        else
        {
            $this->MACs = Mac::orderBy('mac')->get();
        }
        $this->projects = Project::withCount('macs')->orderBy('name')->get();
        $this->maxfilesize = UploadedFile::getMaxFilesize();
        $this->freediskspace = min( disk_free_space("/tmp"), disk_free_space(public_path("uploads")));

        return view('livewire.macs');
    }

    public function exportCSV()
    {
        return response()->streamDownload(function () {
            $fd = fopen('php://output', 'w'); 

            if ( count($this->MACs) )
            {
                fputcsv($fd, array_keys($this->MACs[0]->getAttributes() ));

                foreach ($this->MACs as $mac)
                {
                    fputcsv($fd, $mac->getAttributes());
                }
            }
            fclose($fd);

        }, 'export-mac-'.date('Ymd').'.csv');        
    }

    public function openImportModal()
    {
        $this->isImportOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportOpen = false;
    }
}
