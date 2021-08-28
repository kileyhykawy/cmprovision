<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mac extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id','mac','serial','state'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public static function reserveAssignedMac($project_id, $serial)
    {
        /*
         * We only take an available MAC address if it hasn't already be
         * reserved. This works for concurrent requests to reserve because
         * each request is atomic (find available and mark it as reserved).
         */
        $availableMac = Mac::where('project_id', $project_id)
            ->whereNull('serial')
            ->whereNull('state')
            ->select('id')
            ->orderBy('id')
            ->limit(1);

        $updateCount = Mac::whereIn('id', $availableMac)->update([
            'serial' => $serial,
            'state' => 'reserved'
        ]);

        /*
         * If we didn't update a row, it means that no MAC was available
         */
        if ($updateCount === 0)
        {
            return null;
        }

        $reservedMac = Mac::where('project_id', $project_id)
            ->where('serial', $serial)
            ->where('state', 'reserved')
            ->select('mac')
            ->firstOrFail();

        return $reservedMac->mac;
    }

    public static function commitReservedMac($project_id, $serial, $reserved_mac)
    {
        $updateCount = Mac::where('project_id', $project_id)
            ->where('serial', $serial)
            ->where('state', 'reserved')
            ->where('mac', $reserved_mac)
            ->update(['state' => 'allocated']);
            
        if ($updateCount === 0)
        {
            throw new \Exception($reserved_mac . ' was not reserved for serial ' . $serial);
        }
    }

    public static function rollbackReservedMac($project_id, $serial, $reserved_mac)
    {
        $updateCount = Mac::where('project_id', $project_id)
            ->where('serial', $serial)
            ->where('state', 'reserved')
            ->where('mac', $reserved_mac)
            ->update([
                'serial' => null,
                'state' => null,
                'created_at' => null,
                'updated_at' => null
            ]);
            
        if ($updateCount === 0)
        {
            throw new \Exception($reserved_mac . ' was not reserved for serial ' . $serial);
        }
    }

}
