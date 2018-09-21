<?php

namespace App\Parsers\SH;

use App\Unit;
use App\Guild;

class GuildParser {

    use \App\Parsers\Concerns\ParsesRegex;

    public $data;
    protected $guild;
    protected $gp;
    protected $gpMap;
    protected $zetaMap;
    protected $name;
    protected $url = '';

    public function __construct($guild) {
        $this->url = "https://swgoh.gg/g/${guild}/guild/";
        $this->guild = $guild;
        $this->gpMap = [];
        $this->zetaMap = [];
    }

    public function scrape() {
        $response = guzzle()->get($this->url, ['allow_redirects' => [ 'track_redirects' => true ]]);
        $this->url = head($response->getHeader(config('redirect.history.header')));
        $anAllyCode = $this->getAnAllyCode();

        ini_set('memory_limit', '192M');
        $this->data = swgoh()->getGuild($anAllyCode, SWGOHHelp::FULL_ROSTER);

        return $this;
    }

    protected function getAnAllyCode() {
        $page = goutte()->request('GET', $this->url);
        $slug = $page->filter('table tbody tr td:first-child a')->attr('href');
        return (preg_match('/\/(\d+)\/$/', $slug, $matches)) ? trim($matches[1]) : null;
    }

    public function name() {
        return $this->data['name'];
    }
    public function gp() {
        return $this->data['gp'];
    }
    public function members() {
        return collect($this->data['roster']);
    }
    public function url() {
        return $this->url;
    }
}