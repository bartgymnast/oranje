<?php

namespace App\Jobs;

use Artisan;
use App\ModUser;
use Carbon\Carbon;
use App\Events\ModsFetched;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $needsScrape = ModUser::where('name', $this->user)
            ->whereDate('last_scrape', Carbon::now())
            ->whereTime('last_scrape', '>', Carbon::now()->subMinutes(30))
            ->doesntExist();

        if ($needsScrape) {
            Artisan::call('swgoh:mods', [
                'user' => $this->user
            ]);
        }

        broadcast(new ModsFetched(ModUser::where('name', $this->user)->firstOrFail()));
    }
}
