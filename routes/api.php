<?php

use App\Guild;
use App\Jobs\ProcessGuild;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('client')->get('/guild/scrape/{id}', function(Request $request, $id) {
    $guild = Guild::firstOrNew(['guild_id' => $id]);

    $name = $guild->name ?? 'GUILD ' . $guild->guild_id;

    if (is_null($guild->id)) {
        $guild->name = $name;
        $guild->url = 'not_scraped';
        $guild->gp = 0;
        $guild->save();
    }

    ProcessGuild::dispatch($guild);

    return response()->json([]);
});

Route::middleware('client')->get('/tw/compare/{first}/{second}', function (Request $request, $first, $second) {
    $guild1 = \App\Guild::where(['guild_id' => $first])->first();
    $guild2 = \App\Guild::where(['guild_id' => $second])->first();
    if (is_null($guild1) || is_null($guild2)) {
        $response = [
            'error' => 'Missing at least 1 guild',
        ];
        $response[$first] = is_null($guild1);
        $response[$second] = is_null($guild2);

        return response()->json($response);
    }

    $chars = [
        'DARTHTRAYA' => 'Traya',
        'DARTHREVAN' => 'Revan',
        'DARTHMALAK' => 'Malak',
        'JEDIKNIGHTREVAN' => 'Darth Revan',
        'PADMEAMIDALA' => 'Padmé',
    ];

    $unitQueries = collect($chars)->map(function($name, $unitName) {
        return [
            "sum(case when characters.unit_name = '${unitName}' then 1 else 0 end) as ${unitName}",
            "sum(case when characters.unit_name = '${unitName}' AND characters.gear_level = 12 then 1 else 0 end) as ${unitName}_12",
            "sum(case when characters.unit_name = '${unitName}' AND characters.gear_level = 13 then 1 else 0 end) as ${unitName}_13",
        ];
    })->collapse()->join(', ');

    $data = DB::table('guilds') ->join('members', 'members.guild_id', '=', 'guilds.id') ->join('characters', 'characters.member_id', '=', 'members.id') ->selectRaw("
            guilds.guild_id,
            max(guilds.gp) as gp,
            sum(case when characters.gear_level = 13 then 1 else 0 end) as gear_13,
            sum(case when characters.gear_level = 12 then 1 else 0 end) as gear_12,
            sum(case when characters.gear_level = 11 then 1 else 0 end) as gear_11,
            ${unitQueries}
        ") ->groupBy('guilds.guild_id')
        ->whereIn('guilds.guild_id', [$guild1->guild_id, $guild2->guild_id])
        ->get();

    $g1Data = (array)$data->firstWhere('guild_id', $guild1->guild_id);
    $g2Data = (array)$data->firstWhere('guild_id', $guild2->guild_id);

    $zetas = DB::table('character_zeta')
        ->join('characters', 'character_id', '=', 'characters.id')
        ->join('members', 'characters.member_id', '=', 'members.id')
        ->selectRaw('count(1) as zetas, members.guild_id')
        ->groupBy('members.guild_id')
        ->whereIn('members.guild_id', [$guild1->id, $guild2->id])
        ->get();

    $g1Zetas = (array)$zetas->firstWhere('guild_id', $guild1->id);
    $g2Zetas = (array)$zetas->firstWhere('guild_id', $guild2->id);

    $g1Data['zetas'] = $g1Zetas['zetas'];
    $g2Data['zetas'] = $g2Zetas['zetas'];

    return response()->json([
        $guild1->name => $g1Data,
        $guild2->name => $g2Data,
        'char_keys' => array_keys($chars),
        'char_names' => $chars
    ]);
});
