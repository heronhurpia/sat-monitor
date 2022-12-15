@extends('layouts.app')

@section('content')

@isset($services)
@foreach ( $services as $service ) 
<div class="container">
	<div class="card {{ $service->bouquet_name }}">
		<div class="card-header">
			<div class="row">
				<div class="col-1">
					@if ( $service->codec == 'HEVC' ) 
						<x-hevc-box/>
					@endif

					@if ( $service->video_pid == '0' ) 
						<x-radio-box/>
					@endif
				</div>
						
				<div class="col-3">
					@if ( $service->count_logs )
						<span class="badge rounded-pill bg-danger">{{$service->count_logs}}</span>
					@endif
					@if ( $service->bouquet_name != "" )
						{{ $service->bouquet_name }} /
					@endif
					{{$service->name}}
				</div>

				<div class="col-8">
					<ul>
						<li>{{$service->service_type}}</li>
						<li>Codec: {{ $service->codec }}</li>
						<li>Vídeo: {{ $service->video_pid }} - PCR: {{ $service->pcr_pid }}</li>
						<li>Canal: {{ $service->viewer_channel }}</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="card-body">
			@isset($service->alteracoes)
				<ul>
				@foreach($service->alteracoes as $log)
					<li>{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}} - {{ $log->description}}, alterado à {{ $log->last }} horas</li> 
				@endforeach
				</ul>
			@endisset

			@isset($service->audios)
				@foreach($service->audios as $audio)
					<x-audio :audio="$audio" />
				@endforeach
			@endisset
		</div>
	</div>
</div>
@endforeach
@endisset

<div class="container">
 	@isset($logs)
		<ul>
		@foreach ( $logs as $log )
			<li>
				<div class="row">
					<div class="col-2">
						{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}}
					</div>
					<div class="col-2">
						{{ ( $log->name ) ? $log->name : '' }}
					</div>
					<div class="col-6">
						{{$log->description}}
					</div>
				</div>
			</li>
		@endforeach
		</ul>
	@endisset
</div>
<?php
//	echo '<pre>'; 
//	print_r($data); 
//	echo '</pre>' ;	
?>
</div>
@endsection
 
 