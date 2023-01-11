@extends('layouts.app')

@section('content')

<div class="container">

	<br>
	<div class="row">
		<div class="col-6">
			@isset($transponders)
				<ul>
					@foreach ( $transponders as $t )
						<li>
							<div class="row">
								<div class="col-3">
									{{\Carbon\Carbon::parse($t->created_at)->format('h:i')}}
								</div>
								<div class="col-3">
									{{$t->tsid}}
								</div>
								<div class="col-6">
									{{$t->frequency}}/{{$t->symbol_rate}}/{{$t->polarity}}
								</div>
							</div>
						</li>
					@endforeach
				</ul>
			@endisset
			@isset($services)
				<ul>
					@foreach ( $services as $s )
						<li>
							<div class="row">
								<div class="col-6">
									{{$s->name}}
								</div>
								<div class="col-6">
									{{$s->bouquet_name}}
								</div>
							</div>
						</li>
					@endforeach
				</ul>
			@endisset
		</div>

		<div class="col-6">
			@isset($logs)
				<ul>
					@foreach($logs as $log)
						<li>{{\Carbon\Carbon::parse($log->created_at)->format('h:i')}}
							-  {{ $log->table}}
							-  {{ $log->description}}
						</li> 
					@endforeach
				</ul>
			@endisset
		</div>
	</div>
</div>

<?php
	//echo '<pre>'; 
	//print_r($xpdrs); 
	//echo '</pre>' ;	

//echo '<pre>'; 
//print_r($logs); 
//echo '</pre>' ;	
?>

@endsection
