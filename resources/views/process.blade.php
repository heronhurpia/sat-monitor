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
				<h5 class="mb-0">
					<button class="btn btn-link text-decoration-none" data-bs-toggle="collapse">
					Histórico de alterações
				</h5>
			</div>
 
			<div class="card-body">
				@foreach($logs as $log)
					<p>{{ $log->id }}. {{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}} - {{ $log->description}}</p> 
				@endforeach
			</div>
		</div>
	</div>
	@endisset
</div>

@endsection