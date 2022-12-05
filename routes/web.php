<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SignalController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\ProcessnologController;
use App\Http\Controllers\TodoController;

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

// Gianni - Forçado o uso do HTTPS caso a URL definida na config tenha HTTPS
$scheme = explode(':', config('app.url'))[0];
if(!empty($scheme)) {
	URL::forceScheme($scheme);
}

// Página de execução do pull
Route::get('/pull', 
	function () {
		return view('pull');
	}
);

Route::get('/', 
	function () {
		return view('home');
	}
);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/change-password', [App\Http\Controllers\HomeController::class, 'changePassword'])->name('change-password');
Route::post('/change-password', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('update-password');

Route::get('/processnolog', [ProcessnologController::class, 'index'])->name('processnolog');
Route::get('/process', [ProcessController::class, 'index'])->name('process');
Route::get('/register', function () {})->middleware('auth');

Route::get('/tarefas',[TodoController:: class,'index'])->name('tarefas')->middleware('auth');
Route::post('/tarefas/create',[TodoController:: class,'create']);
Route::post('/tarefas/update',[TodoController:: class,'update']);

Route::get('/lista',[ListaController:: class,'index'])->name('lista')->middleware('auth');
Route::post('/lista/find',[ListaController:: class,'find']);

Route::get('/sinal',[SignalController:: class,'index'])->name('sinal')->middleware('auth');
Route::get('/qualidade',[QualityController:: class,'index'])->name('qualidade')->middleware('auth');

Route::get('/admin',[AdminController:: class,'index'])->name('admin')->middleware('auth');
Auth::routes();

