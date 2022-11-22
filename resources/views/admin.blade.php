@extends('layouts.app')

@section('content')

<div class="container">
	<h2>Lista de canais</h2>
	<h3>Última atualização: {{\Carbon\Carbon::parse($channels->datetime)->format('d/m/Y h:i')}}</h3>
	<br>	
 
	@isset($logsss)
		@foreach ( $logs as $log )
			<hr>
			<div class="alert alert-primary" role="alert">
				<div class="row">
					<div class="col-2">
						{{\Carbon\Carbon::parse($log->updated_at)->format('d/m/Y h:i')}}
					</div>
					<div class="col-9">
						{{$log->description}}
					</div>
				</div>
			</div>
			@endforeach
	@endisset

	@isset($xponders)
		@foreach($xponders as $x) 
			<br><hr>
			<div class="container-fluid bg-light">	
				<!-- Lista de transponders		 -->
				<div class="row {{ $x->alert ? 'text-danger' : ''}}" style="font-size:1.4em;">
					<div class="col-3">
						Frequencia: {{$x->frequency}}
					</div> 
					<div class="col-3">
						{{$x->updated_at}}
					</div>
					<div class="col-6">
						Atualizado à {{$x->interval}} minutos
					</div>
				</div>

				<!-- Lista de alterações do transponder -->
				<div class="row text-danger">
					@isset($x->alteracoes)
						<ul>
						@foreach($x->alteracoes as $a)
							<li>
								<div class="row">
									<div class="col-2">
										{{$a->created_at}}
									</div>
									<div class="col-10">
										{{$a->description}}
									</div>
								</div>
							</li>
						@endforeach
						</ul>
					@endisset
				</div>

				<!-- Lista de serviços -->
				@isset($x->services)
				@foreach($x->services as $service) 
					<div class="row">
						<div class="col">
								{{$service->name}}
							</div> 
							<div class="col">
								{{$service->video_type_id}}
							</div>
							<div class="col">
								Video PID: {{$service->video_pid}}
							</div>
							<div class="col">
								PCR PID: {{$service->pcr_pid}}
							</div>

							<!-- Lista de alterações do serviço -->
							@isset($service->alteracoes)
								<ul>
								@foreach($service->alteracoes as $a)
									<li>
										<div class="row">
											<div class="col-2">
												{{$a->created_at}}
											</div>
											<div class="col-10">
												{{$a->description}}
											</div>
										</div>
									</li>
								@endforeach
								</ul>
							@endisset

							<!-- Lista de áudios -->
							@isset($service->audios)
							@foreach($service->audios as $audio)
							<div class="row">
								<div class="col">
									PID: {{$audio->pid}}
								</div> 
								<div class="col">
									Tipo: {{$audio->audio_type_id}}
								</div>
								<div class="col">
									Idioma: {{$audio->language_id}}
								</div>
							</div>

							<!-- Lista de alterações do áudio -->
							@isset($audio->alteracoes)
								<ul>
								@foreach($audio->alteracoes as $a)
									<li>
										<div class="row">
											<div class="col-2">
												{{$a->created_at}}
											</div>
											<div class="col-10">
												{{$a->description}}
											</div>
										</div>
									</li>
								@endforeach
								</ul>
							@endisset

							@endforeach
							@endisset

						</div>
				@endforeach
				@endisset

			</div>
			@endforeach
	@endisset

	@isset($channels->lineup)
		@foreach(json_decode($channels->lineup) as $transponder)
			@foreach($transponder as $ch)
				
				<br><hr>
				<div class="container bg-light" style="max-width: 90rem;">			
					<h5 class="text-dark">
						<div class="row">
							<div class="mb-2 col-md-3">
								Frequencia: {{$ch->frequency}}
							</div>
							<div class="mb-2 col-md-3">
								S/R: {{$ch->symbol_rate}}
							</div>
							<div class="mb-2 col-md-4">
								Polaridade: {{$ch->polarity}}
						</div>
					</h5>
						
					<div class="row">
						@foreach($ch->services as $service)
							@if ( $service->name != "" ) 
								<div class="col-md-4">
									<ul>
										<strong>{{$service->name}}</strong>
										<li>Service ID: {{$service->service_id}}</li>
										<li>Tipo de video: {{$service->video_codec}}</li>
										<li>PCR PID:{{$service->pcr_pid}}</li>
										<li>Video PID:{{$service->video_pid}}</li>
										<li>PCR PID:{{$service->pcr_pid}}</li>
										@foreach($service->audio_pids as $audio)
											<li>
												<div class="row">
													<div class="col-md-4">Audio: {{$audio->pid}}</div>
													<div class="col-md-4">Idioma: {{$audio->lang}}</div>
													<div class="col-md-4">Codec: {{$audio->codec}}</div>
												</div>
											</li>
										@endforeach
									</ul>
								</div>
							@endif
						@endforeach
					</div>
				</div>
				@endforeach
		@endforeach
	@endisset
</div>
<?php
//	echo '<pre>'; 
//	print_r($data); 
//	echo '</pre>' ;	
?>
</div>
@endsection
 
 