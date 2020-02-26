<?php

namespace App\Util;

use App\Unit;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\Alignment;

trait MetaChars {

    public static function getCompareCharacters() {
        static $chars;
        if (is_null($chars)) {
            $chars = collect([
                'GENERALSKYWALKER' =>    'Gen. Skywalker',
                'DARTHREVAN' =>          'Darth Revan',
                'DARTHMALAK' =>          'Malak',
                'JEDIKNIGHTREVAN' =>     'Revan',
                'PADMEAMIDALA' =>        'Padmé',
                'GRIEVOUS' =>            'Grievous',
                'GEONOSIANBROODALPHA' => 'Geo Alpha',
                'DARTHTRAYA' =>          'Traya',
                'ANAKINKNIGHT'        => 'Anakin',
            ]);

            $units = Unit::whereIn('base_id', $chars->keys())->get();

            $chars = $chars->mapWithKeys(function($name, $id) use ($units) {
                $unit = $units->where('base_id', $id)->first();
                return [$id => ['name' => $name, 'alignment' => strtolower($unit->alignment)]];
            });
        }

        return $chars;
    }
    public static function getKeyCharacters() {
        static $chars;
        if (is_null($chars)) {
            $chars = collect([
                'WATTAMBOR' =>          'Wat Tambor',
                'BASTILASHANDARK' =>    'Bastila',
                'ENFYSNEST' =>          'Enfys Nest',
                'BOSSK' =>              'Bossk',
                'JANGOFETT' =>          'Jango Fett',
            ]);

            $units = Unit::whereIn('base_id', $chars->keys())->get();

            $chars = $chars->mapWithKeys(function($name, $id) use ($units) {
                $unit = $units->where('base_id', $id)->first();
                return [$id => ['name' => $name, 'alignment' => strtolower($unit->alignment)]];
            });
        }

        return $chars;
    }
    public static function getKeyShips() {
        static $chars;
        if (is_null($chars)) {
            $chars = collect([
                'CAPITALNEGOTIATOR' =>  'Negotiator',
                'CAPITALMALEVOLENCE' => 'Malevolence',
                'MILLENNIUMFALCON' =>   'HMF',
                'HOUNDSTOOTH' =>        "Hound's Tooth",
            ]);

            $units = Unit::whereIn('base_id', $chars->keys())->get();

            $chars = $chars->mapWithKeys(function($name, $id) use ($units) {
                $unit = $units->where('base_id', $id)->first();
                return [$id => ['name' => $name, 'alignment' => strtolower($unit->alignment)]];
            });
        }

        return $chars;
    }

    public static function getCompareStats() {
        static $stats;
        if (is_null($stats)) {
            $stats = collect([
                ['stat' => UnitStat::UNITSTATSPEED(), 'key' => 'speed', 'display' => ''],
                ['stat' => UnitStat::UNITSTATMAXHEALTH(), 'key' => 'health', 'display' => ''],
                ['stat' => UnitStat::UNITSTATMAXSHIELD(), 'key' => 'defense', 'display' => ''],
                ['stat' => UnitStat::UNITSTATRESISTANCE(), 'key' => 'tenacity', 'display' => ''],
                ['stat' => UnitStat::UNITSTATACCURACY(), 'key' => 'potency', 'display' => ''],
                ['stat' => UnitStat::UNITSTATATTACKDAMAGE(), 'key' => 'offense', 'display' => 'P'],
                ['stat' => UnitStat::UNITSTATABILITYPOWER(), 'key' => 'offense', 'display' => 'S'],
            ]);
        }

        return $stats;
    }

}
