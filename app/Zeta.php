<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zeta extends Model
{
    use \App\Database\CachesQueries;

    protected $fillable = ['name', 'character_id'];
    public function members() {
        return $this->belongsToMany(Character::class)->withTimestamps();
    }
}
