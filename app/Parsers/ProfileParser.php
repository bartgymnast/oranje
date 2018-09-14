<?php

namespace App\Parsers;

use App\ModUser;
use Carbon\Carbon;

class ProfileParser {

    use Concerns\ParsesRegex;

    protected $user;
    protected $lastUpdate;
    protected $url = '';

    public function __construct($user) {
        $this->url = "https://swgoh.gg/u/${user}/";
        $this->user = $user;
    }

    public function scrape() {
        $response = guzzle()->get($this->url, ['allow_redirects' => [ 'track_redirects' => true ]]);
        $this->url = head($response->getHeader(config('redirect.history.header')));
        $body = (string)$response->getBody();
        $this->lastUpdate = Carbon::parse($this->getUpdatedDateFrom($body));
        return $this->lastUpdate;
    }

    public function hasChanges() {
        $user = ModUser::where('name', $this->user)->first();
        return is_null($user) || $this->lastUpdate->greaterThan($user->last_scrape);
    }

    public function upToDate() {
        return !$this->hasChanges();
    }

    public function getAllyCode() {
        return static::getStringValue($this->url, '/\/([0-9]{9})\/$/');
    }

    private function getUpdatedDateFrom($html) {
        return static::getStringValue($html, '/data-datetime="([^"]+)"/m');
    }
}