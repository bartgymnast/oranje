<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'WelcomeController@index')->name('welcome');
Route::get('/poster', 'WelcomeController@poster')->name('poster');
Route::post('/', 'WelcomeController@store')->name('join.inquiry');

Auth::routes();

Route::prefix('login')->group(function() {
    Route::get('discord', 'Auth\LoginController@redirectToProvider')->name('login.discord');
    Route::get('discord/callback', 'Auth\LoginController@handleProviderCallback');
});

Route::get('/mods', 'ModsController@index')->name('mods');
Route::get('/mods/{user}', 'ModsController@pullUser')->name('mods.user');

Route::get('/u/{user}/{param?}', function($user, $param) {
    return redirect()->away("https://swgoh.gg/u/$user/$param");
});
Route::get('/p/{user}/{param?}', function($user, $param) {
    return redirect()->away("https://swgoh.gg/p/$user/$param");
});
Route::get('podcast.rss', function() {
    return redirect()->away("http://feeds.soundcloud.com/users/soundcloud:users:536817606/sounds.rss");
});
Route::get('discord/{id}', function($id) {
    $response = guzzle()->get("https://discordapp.com/api/guilds/${id}/widget.json");
    return $response->getBody();
});

Route::group(['middleware' => ['auth:web,admin']], function() {
    Route::group(['middleware' => ['active']], function() {
        Route::get('/handbook/{name}', 'HandbookController')->name('handbook');
        Route::get('/guide/{name}', 'GuideController')->name('guide');
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/home/mods', 'ModsController@index')->name('auth.mods');
        Route::put('/home/mods/{user}', 'ModsController@pullUser')->name('auth.mods.user.pull');
        Route::get('/home/mods/{user}', 'ModsController@modsFor')->name('auth.mods.user');
        Route::get('/home/units/{user}', 'ModsController@unitsFor')->name('auth.units.user');
        Route::post('/home/stats', 'ModsController@calculateStats')->name('auth.unit.stats');
    });

    Route::name('approve.')->middleware(['auth:admin'])->group(function() {
        Route::put('/approve/{id}', 'HomeController@approveUser')->name('user');
        Route::put('/approve/admin/{id}', 'HomeController@approveAdmin')->name('admin');
    });

    Route::resource('tw-teams', 'TerritoryCountersController');
    Route::resource('character-mods', 'UnitModPreferenceController');

    Route::get('/units', 'UnitController@index')->name('units');
    Route::get('/guilds', 'GuildController@listGuilds')->name('guilds');
    Route::get('guild/{guild}', 'GuildController@guildGP')->name('guild.guild');
    Route::get('guild/{guild}/mods', 'GuildController@guildMods')->name('guild.modsList');
    Route::put('/guild/{guild}/refresh', 'GuildController@scrapeGuild')->name('guild.refresh');
    Route::get('/guild/{guild}/{team}/{mode?}/{index?}', 'GuildController@listMembers')->name('guild.members');

    Route::get('/character_mods/{id}', 'GuildController@characterMods');

    Route::post('/guilds', 'GuildController@addGuild')->name('guild.add');
    Route::post('/guild/compare', 'GuildController@postGuildCompare')->name('guild.post.compare');

    Route::get('/schwartz', 'GuildController@schwartzGuilds')->name('schwartz.guilds');
    Route::get('/schwartz_mods', 'GuildController@schwartzGuildMods')->name('schwartz.mods');
    Route::get('gp/{guild?}', 'GuildController@listGP')->name('guild.gp');
    Route::get('guild_mods/{guild}', 'GuildController@listMods')->name('guild.mods');

    Route::get('/waiting', 'HomeController@waiting')->name('waiting');

    Route::post('notify', 'HomeController@notify')->name('notify');

    Route::get('/guild-search', 'SearchController@searchGuilds')->name('search.guilds');
});

Route::group(['middleware' => ['auth.or.client:web,admin,bot']], function() {
    Route::get('/relics', 'RelicController@index')->name('relic.recommendations');
    Route::get('/relics/{ally}', 'RelicController@relicMember')->name('member.relic.recommendations');
    Route::get('/compare/{guild1}/{guild2}', 'GuildController@compareGuilds')->name('guild.compare');
    Route::get('/member/compare', 'MemberController@compareMembers')->name('member.compare');
    Route::get('/member/{ally}', 'MemberController@show')->name('member.profile');
    Route::get('/member/{ally}/{team}', 'MemberController@listTeams')->name('member.teams');
});

Route::get('/schwartz_list', 'GuildController@schwartzGuildsImportList')->name('schwartz.import');