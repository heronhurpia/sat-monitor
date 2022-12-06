@isset($services)
	<div class="row">
	@foreach($services as $service) 
		<div class="col-4">
			<div class="card {{ $service->bouquet_name }} {{$service->video_pid == '0' ? 'bg-success bg-opacity-10' : ''}}" style="width: 100%;">
				<div class="card-body">
				  <h5 class="card-title">
						@if ( $service->count_logs )
							<span class="badge rounded-pill bg-danger">{{$service->count_logs}}</span>
						@endif
						{{$service->name}}
					</h5>

					<ul>
						@if ( $service->bouquet_name != "" )
							<li class="{{ $service->bouquet_name }}">
								Rede: {{ $service->bouquet_name }}
							</li>
						@endif
						<li class="{{$service->codec == 'HEVC' ? 'bg-primary bg-opacity-75' : ''}}">
							{{$service->service_type}}
						</li>
						<li class="{{$service->codec == 'HEVC' ? 'bg-primary bg-opacity-75' : ''}}">
							Codec: {{ $service->codec }}
						</li>
						<li>
							Vídeo: {{ $service->video_pid }} - PCR: {{ $service->pcr_pid }}
						</li>
						<li>
							viewer_channel: {{ $service->viewer_channel }}
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

