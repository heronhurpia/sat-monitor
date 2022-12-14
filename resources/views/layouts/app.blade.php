<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Sat Monitor') }}</title>

	<!-- Fonts -->
	<link rel="dns-prefetch" href="//fonts.gstatic.com">
	<link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

	<!-- Scripts -->
	@vite(['resources/sass/app.scss', 'resources/js/app.js'])
 
</head>
<body>
	<div id="app">
		<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
			<div class="container">
				<a class="navbar-brand" href="{{ url('/') }}">
					{{ config('app.name', 'Montage') }}
				</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<!-- Left Side Of Navbar -->
					<ul class="navbar-nav me-auto">

					</ul>

					<!-- Right Side Of Navbar -->
					<ul class="navbar-nav ms-auto">
						<!-- Authentication Links -->
						@auth 
							<li class="nav-item {{ (Request::is('lista')?'h5':'') }}"">
								{!! (Request::is('lista')?"<strong>":"") !!}
								<a class="nav-link" href="{{ route('lista') }}">{{ __('Lista') }}</a>
								{!! (Request::is('lista')?"</strong>":"") !!}
							</li>
							<li class="nav-item {{ (Request::is('qualidade')?'h5':'') }}"">
								{!! (Request::is('qualidade')?"<strong>":"") !!}
								<a class="nav-link" href="{{ route('qualidade') }}">{{ __('Qualidade') }}</a>
								{!! (Request::is('qualidade')?"</strong>":"") !!}
							</li>
							<li class="nav-item {{ (Request::is('tarefas')?'h5':'') }}"">
								{!! (Request::is('tarefas')?"<strong>":"") !!}
								<a class="nav-link" href="{{ route('tarefas') }}">{{ __('Tarefas') }}</a>
								{!! (Request::is('tarefas')?"</strong>":"") !!}
							</li>
						@endauth
						
						@guest
							@if (Route::has('login'))
								<li class="nav-item">
									<a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
								</li>
							@endif
						@else
							<li class="nav-item dropdown">
								<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
									{{ Auth::user()->name }}
								</a>

								<div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

									<a class="dropdown-item" href="{{ route('logout') }}"
									   onclick="event.preventDefault();
													 document.getElementById('logout-form').submit();">
										{{ __('Sair') }}
									</a>

									<a class="dropdown-item" href="{{ route('change-password') }}">Alterar senha</a>

									<!-- Registrar novos usuários -->
									@can('manage_users')
										<a class="dropdown-item" href="{{ route('register') }}">{{ __('Novo usuário') }}</a>
									@endcan 


									<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
										@csrf
									</form>
								</div>
							</li>
						@endguest
					</ul>
				</div>
			</div>
		</nav>

		<main class="py-4">
			@yield('content')
		</main>
	</div>
</body>
</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src='//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js'></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	
@hassection('script-commands')
	@yield('script-commands')
@endif