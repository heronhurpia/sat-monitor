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
					<li>Canais de TV H265: {{$hevc}}</li>
				@endisset
				@isset($radio)
					<li>Canais de rádio: {{$radio}}</li>
				@endisset
				@isset($logs[0])
					<li>{{\Carbon\Carbon::parse($logs[0]->created_at)->format('d/m/Y h:i')}} - {{ $logs[0]->description}}</li>
				@endisset
			</ul>
		</div>
	</div>

	<!-- Lista de logs -->
	<br><hr><br>
	@isset($logs)
	<div class="section">
		<div class="card">
			<div class="card-header">
				Histórico de alterações
			</div>
 
			<div class="card-body">
				<div class="card-body">
					<ul>
						@foreach($logs as $log)
							<li>
								<div class="row">
									<div class="col-2">
										{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}}
									</div>
									<div class="col-2">
										{{$log->name}}
									</div>
									<div class="col-8">
										{{$log->description}}
									</div>
								</div>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
	@endisset
</div>

<?php

//echo '<pre>'; 
//print_r($collection); 
//echo '</pre>' ;	

//echo '<pre>'; 
//print_r($transponders); 
//echo '</pre>' ;	
?>

@endsection