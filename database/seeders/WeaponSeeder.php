<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Weapon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeaponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete all item properties
        DB::table('items')->delete();

        $data = $this->getDataFromFile();
        $this->create($data);
    }

    private function getDataFromFile(): array
    {
        // Read the file line by line
        $properties = file(storage_path('app/Weapons.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Get headers
        $headers = explode("\t", $properties[0]);

        // Loop through the lines
        $data = [];
        for ($line = 1; $line < count($properties); $line++) {
            $split = explode("\t", $properties[$line]);

            $property = [];
            foreach ($split as $index => $value) {
                $header = $headers[$index];
                $property[$header] = $value;
            }

            $data[] = $property;
        }

        return $data;
    }

    private function create(array $data): void
    {
        foreach ($data as $row) {
            if ($row['name'] === 'Expansion' || $row['type'] === 'tpot' || $row['spawnable'] !== '1') {
                continue;
            }

            // Create the base item
            $item = Item::create([
                'name' => $row['name'],
                'code' => $row['code'],
                'type' => $row['type'],
                'gfx_base' => $row['invfile'],
                'gfx_unique' => $row['uniqueinvfile'],
                'gfx_set' => $row['setinvfile'],
                'level_requirement' => $row['levelreq'],
                'width' => $row['invwidth'],
                'height' => $row['invheight'],
                'max_sockets' => $row['gemsockets'] === '' ? 0 : $row['gemsockets'],
                'required_strength' => $row['reqstr'] === '' ? 0 : $row['reqstr'],
                'required_dexterity' => $row['reqdex'] === '' ? 0 : $row['reqdex'],
            ]);

            // Create the weapon-specific entry
            Weapon::create([
                'item_id' => $item->id,
                'min_damage' => $row['mindam'] === '' ? null : $row['mindam'],
                'max_damage' => $row['maxdam'] === '' ? null : $row['maxdam'],
                'min_two_handed_damage' => $row['2handmindam'] === '' ? null : $row['2handmindam'],
                'max_two_handed_damage' => $row['2handmaxdam'] === '' ? null : $row['2handmaxdam'],
                'min_missile_damage' => $row['minmisdam'] === '' ? null : $row['minmisdam'],
                'max_missile_damage' => $row['maxmisdam'] === '' ? null : $row['maxmisdam'],
                'speed' => $row['speed'] === '' ? null : $row['speed'],
                'has_splash' => $row['rangeadder'] !== '' ? true : false,
            ]);
        }
    }
}
