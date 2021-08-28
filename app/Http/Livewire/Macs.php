<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Mac;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class Macs extends Component
{
    public $MACs;
    public $isOpen = false;
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
            $this->MACs = Mac::where('project_id', $this->projectId)->orderBy('id')->get();
        }
        else
        {
            $this->MACs = Mac::orderBy('id')->get();
        }
        $this->projects = Project::withCount('macs')->orderBy('name')->get();

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
}
