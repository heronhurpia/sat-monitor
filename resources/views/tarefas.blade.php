@extends('layouts.app')
@section('content')

<div class="container-fluid">
{{-- Lista de erros --}}
@if ($errors->any())
	@foreach ($errors->all() as $error)
		<div class="materialert error">
			<i class="material-icons">check_circle</i> <span>{{ $error }}</span>
			<button type="button" class="close-alert">×</button>
		</div>
	@endforeach
@endif

@isset ( $mensagens )
	@foreach ( $mensagens as $m)
		<div class="materialert info">
			<i class="material-icons">check_circle</i> <span>{{ $m }}</span>
			<button type="button" class="close-alert">×</button>
		</div>
	@endforeach
@endisset

<!-- Formulário de nova tarefa -->
<div class="container">
	<div class="row">
		<div class="col-10">
			<form action="tarefas/create" method="post">
				@csrf
				<div class="input-group">
					<input hidden class="user_id" name="user_id" value="{{Auth::user()->id}}">
					<input id="description" name="description" type="text" class="form-control" required/>
					<button class="btn btn-outline-secondary" type="submit">Nova tarefa</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Lista de tarefas -->
@isset ( $tarefas )
	<hr>
	<section class="container">
		<ul class="list-group">
			@foreach ( $tarefas as $t)
				<li class="list-group-item {{ ( $t->finished == 0 ) ? 'bg-danger bg-opacity-10' : '' }}">
					<div class="row">
						<div class="col-9">
							<form action="tarefas/update" method="post" class='form-data'>
								@can('manage_users')
									{{ csrf_field() }}
									<input type="hidden" class="tarefa_id" name="tarefa_id" value="{{$t->id}}">
									@if ( $t->finished == 0 )
										<button name="finish" class="btn btn-outline-success" type="submit">Finalizar</button>
									@else
										<button name="reinsert" class="btn btn-outline-danger" type="submit">Reinserir</button>
									@endif
									<button name="delete" class="btn btn-outline-danger" type="submit">Apagar</button>
								@endcan

								{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y')}} 
								- {{ ( null!= $t->solicitante ) ? $t->solicitante : "" }}
								- {{ $t->description}} 
							</form> 
						</div>
						@if ( $t->finished == 1 ) 
							<div class="col-3">
								Finalizado em {{ \Carbon\Carbon::parse($t->updated_at)->format('d/m/Y h:i')}}
							</div>
						@endif
					</div>
				</li>
			@endforeach
		</ul>
	</section>
@endisset

<?php
	//echo '<pre>'; 
	//print_r($users); 
	//echo '</pre>' ;	
?>

@endsection

@section('script-commands')
<script	type='text/javascript'>
	$(document).ready(function(){
	});
</script>
@endsection