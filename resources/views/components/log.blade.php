@isset($logs)
	<ul class='text-danger detailed-data'>
		@foreach($logs as $log)
			<li>{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}} - {{ $log->description}}</li> 
		@endforeach
	</ul>
@endisset
