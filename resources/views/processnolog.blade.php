@extends('layouts.app')

@section('content')

<div class="container">

	<br>
	<div class="row">
		<div class="col-5">
			<h3>Resumo:</h3> 
			<ul>
				@isset($tv)
					<li>Canais de TV ( H264 e H265): {{$tv}}</li>
				@endisset
				@isset($tv)
					<li>Canais de TV H265: {{$hevc}}</li>
				@endisset
				@isset($radio)
					<li>Canais de rádio: {{$radio}}</li>
				@endisset
			</ul>
		</div>
	</div>
</div>

<?php

//echo '<pre>'; 
//print_r($transponders); 
//echo '</pre>' ;	
?>

@endsection
