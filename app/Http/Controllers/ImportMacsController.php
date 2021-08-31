<?php
/*
 * Handling file upload with normal controller. As livewire still has some issues with large files
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Mac;
use App\Imports\MacsImport;
use MaatWebsite\Excel\Facades\Excel;

class ImportMacsController extends Controller
{
    public function store(Request $req)
    {
        @set_time_limit(86400);
        @ignore_user_abort(true);

        Validator::extend('file_extension',
        function($attribute, $value, $parameters, $validator) {
            if (!$value instanceof \Illuminate\Http\UploadedFile) {
                return false;
            }
    
            $extension = $value->getClientOriginalExtension();
            return $extension != '' && in_array($extension, $parameters);
        }, "Only .csv files are supported");

        $data = $req->validate([
            'project_id' => 'required|integer',
            'csv' => 'required|file|file_extension:csv',
        ]);

        $project_id = $data['project_id'];
        $pathname = $data['csv']->getPathname();
        $importer = new MacsImport($project_id);
        
        $importer->import($pathname, null, \Maatwebsite\Excel\Excel::CSV);

        session()->flash('message', 'MACs loaded.');        
        return redirect()->route('macs');
    }
}
