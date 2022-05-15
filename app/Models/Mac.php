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

    private static function alreadyAssignedMac($project_id, $serial)
    {
        /*
         * We first check to see if the current device (by serial number) was
         * already allocated a MAC address either because it was flashed before
         * (`allocated` state) or was in progress but something happened that
         * it never finished (`reserved` state - this really shouldn't happen
         * but going to check anyways). If there is a MAC address assigned, we
         * use that.
         */
        $alreadyMac = Mac::where('project_id', $project_id)
            ->where('serial', $serial)
            ->whereNotNull('state')
            ->select('id')
            ->orderBy('id')
            ->limit(1);

        $updateCount = Mac::whereIn('id', $alreadyMac)->update([
            'serial' => $serial,
            'state' => 'reserved'
        ]);

        /*
         * If we didn't update a row, it means that device did not have a MAC
         * assigned
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

    public static function reserveAssignedMac($project_id, $serial)
    {
        /*
         * We first check to see if the current device (by serial number) was
         * already allocated a MAC address and use that. If we don't, we
         * try to allocate an available MAC address
         */
        $alreadyAssignedMac = Mac::alreadyAssignedMac($project_id, $serial);
        if (!is_null($alreadyAssignedMac))
        {
            return $alreadyAssignedMac;
        }

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
