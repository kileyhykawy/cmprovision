<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Script;

define('SCRIPT_NAME',
    'Set assigned MAC address via forced_mac_address (system)');

class AddSetMacScript extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add script for setting assigned MAC address to device
        $script = <<<'EOF'
#!/bin/sh
set -e

mkdir -p /mnt/boot
mount -t vfat $PART1 /mnt/boot
# Add newline before in case config.txt does not have one
echo -e "\nforce_mac_address=$ASSIGNED_MAC" >> /mnt/boot/config.txt
umount /mnt/boot
EOF;

        Script::create(
            array(
                'name' => SCRIPT_NAME,
                'script_type' => 'postinstall',
                'bg' => false,
                'priority' => 200,
                'script' => $script
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Script::where('name', SCRIPT_NAME)->delete();
    }
}
