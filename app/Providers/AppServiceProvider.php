<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;
use \Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // https://github.com/spatie/laravel-activitylog/issues/39
        Activity::saving(function (Activity $activity) {
            $agent = new Agent();
            $activity->properties = $activity->properties->put('agent', [
                'ip' => Request()->ip(),
                'browser' => $agent->browser(),
                'browser-version' => $agent->version($agent->browser()),
                'os' => $agent->platform(),
                'os-version' => $agent->version($agent->platform()),
            ]);
        });
    }
}
