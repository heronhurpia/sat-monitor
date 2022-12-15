@isset($audio)
<div class="section">
	<div class="card">
		<div class="card-header">
			<div class="row">
				@if ( $audio->count_logs )
					<div class="col">
						<span class="badge rounded-pill bg-danger">{{$audio->count_logs}}</span>
					</div>
				@endif

				<div class="col">ID: {{$audio->id}}</div>
				<div class="col">PID: {{$audio->pid}}</div>
				<div class="col">Idioma: "{{$audio->language}}"</div>
				<div class="col">Tipo: {{$audio->audio_type}}</div>
			</div>
		</div>

		@isset($audio->alteracoes)
			<div class="card-body">
				<ul>
				@foreach($audio->alteracoes as $log)
					<li>{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}} - {{ $log->description}}, alterado à {{ $log->last }} horas</li>
				@endforeach
				</ul>
			</div>
		@endisset
	</div>
</div>
@endisset

