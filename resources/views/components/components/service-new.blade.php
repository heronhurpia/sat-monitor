@isset($services)
	<div class="row">		
		@foreach($services as $service) 
			<div class="col-4 {{$service->codec == 'HEVC' ? 'bg-primary bg-opacity-10' : ''}}  {{$service->video_pid == '0' ? 'bg-success bg-opacity-10' : ''}} ">
				<span class="{{$service->has_logs ? 'text-danger' : 'text-black'}}" 
					style="font-size:1.2em">
					@if ( $service->count_logs )
						<span class="badge rounded-pill bg-danger">{{$service->count_logs}}</span>
					@endif
					{{$service->name}}
					@if ( $service->codec == 'HEVC' )
						- HEVC
					@endif
				</span>

				<!-- Lista logs -->
				<x-components.log :logs="$service->alteracoes" />

				<!-- Exibe canais de áudios -->
				<x-components.audio-new :audios="$service->audios" />
				
			</div>
		@endforeach
	</div>
@endisset

