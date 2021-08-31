<?php

namespace App\Imports;

use App\Models\Mac;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MacsImport implements ToModel, WithValidation
{
    use Importable;
    private $project_id;

    public function __construct(int $project_id)
    {
        $this->project_id = $project_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Mac([
            'project_id' => $this->project_id,
            'mac' => $row[0]
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => [
                'required',
                'regex:/^([0-9a-f]{2}:){5}([0-9a-f]{2})$/'
            ]
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.regex' => 'MAC address format with lowercase letters, e.g ab:12:cd:34:ef:56'
        ];
    }

    public function batchSize(): int
    {
        return 250;
    }

    public function chunkSize(): int
    {
        return 250;
    }
}
