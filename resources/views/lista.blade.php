@extends('layouts.app')

@section('content')

<div class="container">

	<br>
	<div class="row">
		<div class="col-5">
			<h3>Resumo:</h3>
			<ul>
				@isset($tv)
					<li>Canais de TV ( H264 e H265): {{$tv}}</li>
				@endisset
				@isset($tv)
					<li>
						<span class="bg-primary bg-opacity-10">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span>
						Canais de TV H265: {{$hevc}}
					</li>
				@endisset
				@isset($radio)
					<li>
						<span class="bg-success bg-opacity-10">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span>
						Canais de rádio: {{$radio}}
					</li>
				@endisset
				@isset($logs[0])
					<li>{{\Carbon\Carbon::parse($logs[0]->created_at)->format('d/m/Y h:i')}} - {{ $logs[0]->description}}</li>
				@endisset
			</ul>
		</div>
		<div class="col-3">
			<form action="lista/find" method="post">
				@csrf
				<div class="input-group">
  					<input name="inicio" id="inicio" type="date" class="form-control" value="{{\Carbon\Carbon::parse($inicio)->format('Y-m-d')}}"/>
					<button class="btn btn-outline-secondary" type="submit">Filtrar</button>
				</div>
			</form>
		</div>
	</div>
	
	<!-- Lista de logs -->
	<br><hr><br>
	@isset($logs)
	<div class="section">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0">
					<button
						class="btn btn-link collapsed text-decoration-none" data-bs-toggle="collapse" 
						data-bs-target="#log_list"  
						aria-expanded="false" 
						aria-controls="log_list">
					Histórico de alterações
				</h5>
			</div>

			<div id="log_list" class="collapse">
				<div class="card-body">
					@foreach($logs as $log)
						<p>{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}} - {{ $log->description}}</p> 
					@endforeach
				</div>
			</div>
		</div>
	</div>
	@endisset

	<!-- Lista de transponders -->
	@isset($transponders)
		@foreach($transponders as $transponder) 
			<x-components.transponder-new :transponder="$transponder" />
		@endforeach
	@endisset

	<br><hr><br>
	<h2>Lista de canais detalhada</h2>
	@isset($transponders)
		@foreach($transponders as $transponder) 
			<x-components.transponder :transponder="$transponder" />
			@endforeach
	@endisset

</div> 

<?php
//	echo '<pre>'; 
//	print_r($list); 
//	echo '</pre>' ;	
?>
@endsection