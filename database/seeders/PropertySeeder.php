<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete all item properties
        Property::query()->delete();

        $data = $this->getPropertiesFromFile();
        $this->createProperties($data);
    }

    private function getPropertiesFromFile(): array
    {
        // Read the file line by line
        $properties = file(storage_path('app/Properties.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

    private function createProperties(array $properties): void
    {
        foreach ($properties as $property) {
            DB::beginTransaction();

            try {
                // Create the property
                $item = Property::create([
                    'code' => $property['code'],
                    'done' => $property['*done'] === '' ? false : true,
                    'desc' => $property['*desc'],
                    'param' => $property['*param'],
                    'min' => $property['*min'] === '' ? null : $property['*min'],
                    'max' => $property['*max'] === '' ? null : $property['*max'],
                ]);

                // Create related stats
                for ($i = 1; $i <= 7; $i++) {
                    if (!empty($property["stat$i"])) {
                        $item->propertyStats()->create([
                            'set' => $property["set$i"] === '' ? null : $property["set$i"],
                            'value' => $property["val$i"] === '' ? null : $property["val$i"],
                            'function' => $property["func$i"] === '' ? null : $property["func$i"],
                            'stat' => $property["stat$i"] === '' ? null : $property["stat$i"],
                            'stat_number' => $i,
                        ]);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }
        }
    }
}
