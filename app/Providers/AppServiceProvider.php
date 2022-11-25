<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
		//
		// Should return TRUE or FALSE
		//Gate::define('manage_users', function(User $user) {
		//	return $user->is_admin == 1;
		//});

		Gate::define('manage_users', function(User $user) {
			return $user->role == 'admin' ;
		});
	}
}
