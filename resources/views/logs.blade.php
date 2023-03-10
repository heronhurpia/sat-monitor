@extends('layouts.app')

@section('content')

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
							<li class="list-group-item">
								<div class="row">
									<div class="col-2">
										<input class='check' type='checkbox'>
										<label style='margin-left: .25rem'>process</label>
									</div>
									<div class="col-2">
										<input class='check' type='checkbox'>
										<label style='margin-left: .25rem'>audios</label>
									</div>
									<div class="col-2">
										<input class='check' type='checkbox' checked>
										<label style='margin-left: .25rem'>services</label>
									</div>
									<div class="col-2">
										<input class='check' type='checkbox' checked>
										<label style='margin-left: .25rem'>transponders</label>
									</div>
								</div>
							</li>

							@foreach($logs as $log)
								<li class="list-group-item {{$log->table}}">
									<div class="row">
										<div class="col-2">
											{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}}
										</div>
										<div class="col-2">
											{{$log->table}}
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
	// echo '<pre>'; 
	// print_r($logs); 
	// echo '</pre>' ;	
?>
@endsection



@section('script-commands')
<script type="text/javascript"> 

	/** Função será executada após a página ser carregada */
	$(document).ready(function(){

		/** Cria chackbox para todas as linhas iniciais */
		createCheckbox();
	});

	// Mostra/oculta itens ao carregar a página
	function createCheckbox(){

		$('.check').each(function(ndx) {
			var field = $(this).closest('div').find('label').html();
			if ($(this).is(':checked')) {
				$('.'+field).show();
			}
			else {
				$('.'+field).hide();
			}
		});
	}

	// Altera mostrar/oculta itens da lista
	$(document).on('change','.check',function() {
		var field = $(this).closest('div').find('label').html();
		if ($(this).is(':checked')) {
			$('.'+field).show();
		}
		else {
			$('.'+field).hide();
		}
	});

</script>
@endsection
