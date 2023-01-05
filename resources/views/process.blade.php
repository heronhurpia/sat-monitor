@extends('layouts.app')

@section('content')

<div class="container">

	<!-- Lista de logs -->
	<br>
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

	<!-- Lista de logs -->
	<br><hr><br>
	@isset($locks)
		<div class="section">
			<div class="card">
				<div class="card-header">
					Resultados das últimas tentativas de sintonia
				</div>
	
				<div class="card-body">
					<div class="card-body">
						<ul>
							@foreach($locks as $lock)
								<li>
									<div class="row">
										<div class="col-2">
											{{$lock->frequency}}
										</div>
										<div class="col-2">
											{{$lock->OK}}
										</div>
										<div class="col-2">
											{{$lock->Failed}}
										</div>
										<div class="col-2">
											{{$lock->Last}}
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
	//print_r($locks); 
	//echo '</pre>' ;	

//echo '<pre>'; 
//print_r($transponders); 
//echo '</pre>' ;	
?>

@endsection