<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningUnitTests()) {
            Schema::defaultStringLength(191);
        }
        if ($this->app->environment('local', 'testing')) {
            $this->printSqlToLog();
        }
        $this->registerRules();
        $this->addCustomFilters();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->addAcceptableJsonType();
    }

    /**
     * Add "application/json" to the "Accept" header for the current request.
     */
    protected function addAcceptableJsonType()
    {
        $this->app->rebinding('request', static function ($app, $request) {
            if (! $request->is('api/*')) {
                return;
            }

            $accept = $request->header('Accept');

            if (Str::contains($accept, ['/json', '+json'])) {
                return;
            }

            $accept = rtrim('application/json,' . $accept, ',');

            $request->headers->set('Accept', $accept);
            $request->server->set('HTTP_ACCEPT', $accept);
            $_SERVER['HTTP_ACCEPT'] = $accept;
        });
    }

    /**
     * Print sql logs
     */
    protected function printSqlToLog()
    {
        \DB::listen(static function ($query) {
            $tmp       = str_replace('?', '"' . '%s' . '"', $query->sql);
            $qBindings = [];
            foreach ($query->bindings as $key => $value) {
                if (is_numeric($key)) {
                    $qBindings[] = $value;
                } else {
                    $tmp = str_replace(':' . $key, '"' . $value . '"', $tmp);
                }
            }
            // $tmp = vsprintf($tmp, $qBindings);
            $tmp = str_replace("\\", "", $tmp);
            \Log::info(' execution time: '.$query->time.'ms; '.$tmp."\n\n\t");
            \Log::info(json_encode($qBindings, JSON_UNESCAPED_UNICODE)."\n\n\t");
        });
    }

    /**
     * Register custom rules
     *
     * @return void
     */
    protected function registerRules()
    {
        $rules = [
            'maxlength' => [
                'validate' => '\App\Foundation\Rules\StrRule@maxlength',
                'message'  => 'The :attribute may not be greater than {0} characters.',
                'replacer' => '\App\Foundation\Rules\StrRule@replacer',
            ],
            'minlength' => [
                'validate' => '\App\Foundation\Rules\StrRule@minlength',
                'message'  => 'The :attribute must be at least {0} characters.',
                'replacer' => '\App\Foundation\Rules\StrRule@replacer',
            ],
            'phone'     => [
                'validate' => '\App\Foundation\Rules\StrRule@phone',
                'message'  => 'The :attribute is not a valid phone number',
            ],
        ];

        foreach ($rules as $name => $rule) {
            Validator::extend($name, $rule['validate'], $rule['message']);
            if (! isset($rule['replacer'])) {
                continue;
            }

            Validator::replacer($name, $rule['replacer']);
        }
    }


    public function customFilters() {
        return [
            'json' => function($value, $options = []) {
                return json_encode($value);
            },
            'boolToInt' => function($value, $options = []) {
                return $value == true ? 1 : 0;
            },
            'replace' => function($value, $options = []) {
                return str_replace($options[0], $options[1], $value);
            },
            'float' => static function ($value, $options = []) {
                return (float)($value);
            },
            'arrayToString' => static function ($value, $options = []) {
                if (is_array($value)) {
                    return implode(',', $value);
                }
                return $value;
            },
        ];
    }

    /**
     *  Add custom fields to the Sanitizer
     *
     *  @return void
     */
    public function addCustomFilters()
    {
        foreach ($this->customFilters() as $name => $filter) {
            app('sanitizer')->extend($name, $filter);
        }
    }
}
