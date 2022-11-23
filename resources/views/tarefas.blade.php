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
							<div class="col-8">
								@if ( Auth::user()->id == 1 )
									<form action="tarefas/update" method="post" class='form-data'>
										{{ csrf_field() }}
										<input type="hidden" class="tarefa_id" name="tarefa_id" value="{{$t->id}}">
										<input type="hidden" class="user_id" name="user_id" value="{{Auth::user()->id}}">
										<button name="finish" class="btn btn-outline-success" type="submit">Finalizar</button>
										<button name="reinsert" class="btn btn-outline-danger" type="submit">Reinserir</button>
										<button name="delete" class="btn btn-outline-danger" type="submit">Apagar</button>
										{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y')}} - {{ $t->description}} 
									</form>
								@else
									{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y')}} - {{ $t->description}} 
								@endif
							</div>
							@if ( $t->finished == 1 ) 
							<div class="col-4">
								Finalizado em {{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y')}}
							</div>
							@endif

						</div>
						
						
					</li>
				@endforeach
			</ul>
		</section>
	@endisset


@endsection

@section('script-commands')
<script	type='text/javascript'>
	$(document).ready(function(){
	});
</script>
@endsection