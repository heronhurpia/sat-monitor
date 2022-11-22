@isset($audios)
	<ul>    
		@foreach ( $audios as $audio )
			<li>
				@if ( $audio->count_logs )
					<span class="badge rounded-pill bg-danger">{{$audio->count_logs}}</span>
				@endif
				pid:{{$audio->pid}}, {{$audio->language}} - {{$audio->audio_type}}

				<div style="font-size:0.8em">
					<x-components.log :logs="$audio->alteracoes"/>
				</div>
			</li>
		@endforeach
	</ul>
@endisset
