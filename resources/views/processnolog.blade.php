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
	//print_r($xpdrs); 
	//echo '</pre>' ;	

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