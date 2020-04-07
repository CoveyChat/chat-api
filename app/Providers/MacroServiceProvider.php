<?php

namespace App\Providers;

use App\Providers\Facades\ApiResponseFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;

class MacroServiceProvider extends ServiceProvider
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

    public function boot()
    {
    	/**
    	 * Performs a whereIn but only if a condition is met. Also can handle non-array single values
    	 */
    	Builder::macro('whereInIf', function ($condition, $column, $array) {
    		if ($condition) {
    			if(is_array($array)) {
    				return $this->whereIn($column, $array);
    			} else if(is_string($array)) {
    				return $this->where($column, '=', $array);
    			}
    		}

    		return $this;
    	});

    	/**
    	 * Performs a where but only if a condition is met
    	 */
    	Builder::macro('whereIf', function ($condition, $column, $operator, $value) {
    		if ($condition) {
    			return $this->where($column, $operator, $value);
    		}

    		return $this;
        });

        /**
         * Api success/error response extension for consistency
         */
        Response::macro('api', function () {
            return new ApiResponseFacade();
        });
    }
}
