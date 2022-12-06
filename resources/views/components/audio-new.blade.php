@if ( count($audios) > 0 )
	<div class="card detailed-data">
		<div class="card-body {{$cor}}">
			<h5 class="card-title">Canais de áudio</h5>
			<ul>    
				@foreach ( $audios as $audio )
					<li>
						@if ( $audio->count_logs )
							<span class="badge rounded-pill bg-danger">{{$audio->count_logs}}</span>
						@endif
						pid:{{$audio->pid}}, {{$audio->language}} - {{$audio->audio_type}}

						<div style="font-size:0.8em">
							<x-log :logs="$audio->alteracoes"/>
						</div>
					</li>
				@endforeach
			</ul>
		</div>
	</div>
@endif

