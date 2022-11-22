@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card">
				<div class="card-header">
					<h4>Monitoramento de satélites</h4>
				</div>

				<div class="card-body">
					@if (session('status'))
						<div class="alert alert-success" role="alert">
							{{ session('status') }}
						</div>
					@endif

					@auth 
						<p>Escolha uma das opções de monitoramento na barra acima</p>
					@endauth

					@guest
						<p>Para ter acesso a todos os recursos de monitoramento é necessário fazer o login</p>
						<p>Clique no canto superior direito no atalho "Login"</p>
						<p>Caso não tenha acesso, solicitar a heron@centurybr.com.br ou gianni.rossi@centurybr.com.br</p>
					@endguest

				</div>
			</div>
		</div>
	</div>
</div>
@endsection
