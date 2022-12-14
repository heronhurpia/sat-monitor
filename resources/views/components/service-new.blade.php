@isset($services)
	<div class="row">
	@foreach($services as $service) 
		<div class="col-4">
			<div class="card {{ $service->bouquet_name }}" style="width: 100%;">
				<div class="card-body">
				  <h5 class="card-title">
						@if ( $service->count_logs )
							<span class="badge rounded-pill bg-danger">{{$service->count_logs}}</span>
						@endif

						@if ( $service->codec == 'HEVC' ) 
							<x-hevc-box/>
						@endif

						@if ( $service->video_pid == '0' ) 
							<x-radio-box/>
						@endif
						
						@if ( $service->bouquet_name != "" )
							{{ $service->bouquet_name }} /
						@endif
						{{$service->name}}
					</h5>

					<ul class="detailed-data">
						<li>
							{{$service->service_type}}
						</li>
						<li>
							Codec: {{ $service->codec }}
						</li>
						<li>
							Vídeo: {{ $service->video_pid }} - PCR: {{ $service->pcr_pid }}
						</li>
						<li>
							Canal: {{ $service->viewer_channel }}
						</li>
					</ul>

		  			<!-- Lista logs -->
					<x-log :logs="$service->alteracoes" />
				</div>

				<!-- Exibe canais de áudios -->
				<x-audio-new :audios="$service->audios" :cor="$service->bouquet_name"/>
	
			 </div>
		</div>
	@endforeach
	</div>
@endisset

