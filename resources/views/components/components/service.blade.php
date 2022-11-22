@isset($service)
<div class="section">
	<div class="card">
		<div class="card-header">
			<h5 class="mb-0">
				<button
					class="btn {{$service->has_logs ? 'btn-link' : 'btl-flat'}} collapsed text-decoration-none" 
					data-bs-toggle="collapse" 
					data-bs-target="{{'#id_' . $service->id}}"  
					aria-expanded="false" 
					aria-controls="{{'id_' . $service->id}}">
				{{$service->name}}
				{{ ( $service->video_pid ) == 0 ? "- Rádio " : "" }}
			</h5>
		</div>

		<div id="{{'id_' . $service->id}}" class="collapse">
			<div class="card-body">
				@isset($service->alteracoes)
					<ul>
					@foreach($service->alteracoes as $log)
						<li>{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}} - {{ $log->description}}, alterado à {{ $log->last }} minutos</li> 
					@endforeach
					</ul>
				@endisset

				@isset($service->audios)
					@foreach($service->audios as $audio)
						<x-components.audio :audio="$audio" />
					@endforeach
				@endisset
			</div>
		</div>
	</div>
</div>
@endisset