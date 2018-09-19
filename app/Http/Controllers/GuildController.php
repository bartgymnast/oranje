<?php

namespace App\Http\Controllers;

use Artisan;
use App\Unit;
use App\Guild;
use Illuminate\Http\Request;
use App\Jobs\ProcessSchwartzGuilds;

class GuildController extends Controller
{
    public function listGuilds() {
        return view('guilds', [
            'guilds' => Guild::orderBy('schwartz', 'desc')->orderBy('gp', 'desc')->get()
        ]);
    }

    public function addGuild(Request $request) {
        $validated = $request->validate([
            'guild' => 'required|integer'
        ]);

        Artisan::call('pull:guild', [
            'guild' => $validated['guild']
        ]);

        return redirect()->route('guilds')->with('guildStatus', "Guild added");
    }

    public function scrapeGuild($guild) {
        $guild = Guild::findOrFail($guild);

        ProcessSchwartzGuilds::dispatch($guild);

        return redirect()->route('guilds')->with('guildStatus', "Guild scrape queued");
    }

    public function listMembers($guild, $team) {
        $guild = Guild::findOrFail($guild);

        $highlight = "gear";
        switch ($team) {
            case 'str':
                $teams = [
                    'RJT' => ['REYJEDITRAINING', 'BB8', 'R2D2_LEGENDARY', 'REY', 'RESISTANCETROOPER', 'VISASMARR', 'HERMITYODA'],
                    'Chex' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'DEATHTROOPER', 'CHIRRUTIMWE', 'PAO', 'CT7567', 'ANAKINKNIGHT'],
                    'Nightsisters' => ['ASAJVENTRESS', 'DAKA', 'NIGHTSISTERZOMBIE', 'MOTHERTALZIN', 'TALIA', 'NIGHTSISTERACOLYTE', 'NIGHTSISTERINITIATE'],
                ];
                break;
            case 'rjt':
                $teams = [
                    'RJT' => ['REYJEDITRAINING', 'REY', 'BB8', 'FINN', 'SMUGGLERHAN', 'SMUGGLERCHEWBACCA'],
                ];
                $highlight = 'stars';
                break;
            case 'tw':
                $teams = [
                    'Traya' => ['DARTHTRAYA', 'DARTHSION', 'DARTHNIHILUS', 'SITHTROOPER', 'VISASMARR', 'GRANDADMIRALTHRAWN', 'ENFYSNEST', 'WAMPA'],
                    'KRU' => ['KYLORENUNMASKED', 'KYLOREN', 'FIRSTORDEROFFICERMALE', 'FIRSTORDEREXECUTIONER', 'FIRSTORDERTROOPER'],
                    'Bounty Hunters' => ['BOSSK', 'BOBAFETT', 'GREEDO', 'DENGAR', 'EMBO', 'BARRISSOFFEE'],
                    'Jedi' => ['BASTILASHAN', 'GRANDMASTERYODA', 'GENERALKENOBI', 'FULCRUMAHSOKA', 'EZRABRIDGERS3'],
                    'Smugglers' => ['QIRA', 'ZAALBAR', 'YOUNGCHEWBACCA', 'L3_37', 'ENFYSNEST'],
                    'Palp' => ['EMPERORPALPATINE', 'VADER', 'GRANDMOFFTARKIN', 'TIEFIGHTERIMPERIAL', 'ROYALGUARD'],
                ];
                break;

            default:
                $teams = [];
                break;
        }

        return view('members', [
            'members' => $guild->members()->with('characters.zetas')->orderBy('player')->get(),
            'units' => Unit::all(),
            'teams' => $teams,
            'highlight' => $highlight,
        ]);
    }

    public function schwartzGuilds() {
        return view('gp', [
            'guilds' => Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get()
        ]);
    }

    public function listGP($guild = null) {
        $guild = is_null($guild) ? Guild::where('schwartz', '1') : Guild::findOrFail($guild);

        return response()->json($guild->members);
    }
}
