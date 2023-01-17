@extends('layouts.app')

@section('content')

<div class="toast-container position-fixed end-0 p-3">
	<div class="toast ml-auto" role="alert" data-delay="700" data-autohide="false">
		<div class="toast-header">
			<strong class="mr-auto text-primary">Aviso!!!</strong>
		 </div>
		 <div class="toast-body">
			<p>Nova varredurua executada</p>
			<p>Verifique o resultado dos logs</p>
		</div>
	</div>
</div>

<div class="container">
	@isset($locks)
		<div class="section">
			<div class="card">
				<div class="card-header">
					Última sintonia
				</div>
	
				<div class="card-body">
					<div class="card-body">
						<ul class="list-group">
							<li class="list-group-item">
								<div class="row h5">
									<div class="col-3">
										Frequencia
									</div>
									<div class="col-2">
										Ok
									</div>
									<div class="col-2">
										Failed
									</div>
									<div class="col-2">
										Last
									</div>
								</div>
							</li>

							@foreach($locks as $index=>$l)
							<li class="list-group-item">
								<div class="row">
									<div class="col-3">
										{{$index}}). {{$l->frequency}}
									</div>
									<div class="col-2">
										{{$l->OK}}
									</div>
									<div class="col-2">
										{{$l->Failed}}
									</div>
									<div class="col-2">
										{{$l->Last}}
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

<div class="container">
	@isset($xpondersss)
		<div class="section">
			<div class="card">
				<div class="card-header">
					Lista salva no dB
				</div>
	
				<div class="card-body">
					<div class="card-body">
						<ul class="list-group">
							@foreach($xponders as $index => $x)
								<li class="list-group-item">
									<div class="row">
										<div class="col-3">
											{{$index}}). {{$x->frequency}}
										</div>
										<div class="col-3">
											{{$x->symbol_rate}}
										</div>
										<div class="col-3">
											{{$x->polarity}}
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

<div class="container">
	@isset($transponderssss)
		<div class="section">
			<div class="card">
				<div class="card-header">
					Lista resultante da última varredura 
				</div>
	
				<div class="card-body">
					<div class="card-body">
						<ul class="list-group">
							@foreach($transponders as $index =>$t)
								<li class="list-group-item">
									<div class="row">
										<div class="col-3">
											{{$index}}). {{$t->frequency}}
										</div>
										<div class="col-3">
											{{$t->symbol_rate}}
										</div>
										<div class="col-3">
											{{$t->polarity}}
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

<div class="container">
	@isset($logs)
		<div class="section">
			<div class="card">
				<div class="card-header">
					Histórico de alterações
				</div>
	
				<div class="card-body">
					<div class="card-body">
						<ul class="list-group">
							@foreach($logs as $log)
								<li class="list-group-item {{$log->table}}">
									<div class="row">
										<div class="col-3">
											{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i')}}
										</div>
										<div class="col-2">
											{{$log->table}}
										</div>
										<div class="col-7">
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
	//print_r($logs); 
	//echo '</pre>' ;	
?>

@endsection

@section('script-commands')
<script type="text/javascript"> 
	$(document).ready(function(){
		window.setTimeout(function() {
	   	window.location.href = 'http://192.168.1.31:8000/processnolog';
		}, 13*60*1000);

		$('.toast').toast('show');
	});
	
</script>
@endsection