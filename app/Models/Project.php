<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['name','device','storage','image_id','label_id','label_moment','eeprom_firmware','eeprom_settings','verify','assign_mac'];
    protected $casts = ['verify' => 'boolean','assign_mac' => 'boolean'];

    function image()
    {
        return $this->belongsTo(Image::class);
    }

    function label()
    {
        return $this->belongsTo(Label::class);
    }

    function scripts()
    {
        return $this->belongsToMany(Script::class);
    }

    function cms()
    {
        return $this->hasMany(Cm::class);
    }

    function macs()
    {
        return $this->hasMany(Mac::class);
    }

    function isActive()
    {
        return $this->id == Project::getActiveId();
    }

    static function getActive()
    {
        $activeId = self::getActiveId();
        if (!$activeId)
            return null;

        return Project::find($activeId);
    }

    static function getActiveId()
    {
        $s = Setting::find('active_project');
        if (!$s)
            return null;
        return intval($s->value);
    }

    function delete()
    {
        if ($this->getActiveId() == $this->id)
            Setting::destroy('active_project');

        parent::delete();
    }

    function reserveAssignedMac($serial)
    {
        if (!$this->assign_mac)
            throw new \Exception('Assign MAC not enabled for project');

        return Mac::reserveAssignedMac($this->id, $serial);
    }

    function commitReservedMac($serial, $reserved_mac)
    {
        if (!$this->assign_mac)
            throw new \Exception('Assign MAC not enabled for project');

        return Mac::commitReservedMac($this->id, $serial, $reserved_mac);
    }

    function rollbackReservedMac($serial, $reserved_mac)
    {
        if (!$this->assign_mac)
            throw new \Exception('Assign MAC not enabled for project');

        return Mac::rollbackReservedMac($this->id, $serial, $reserved_mac);
    }

}
