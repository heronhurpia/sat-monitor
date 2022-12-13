@extends('layouts.app')

@section('content')

<div class="container">
	<br>

	{{-- Resumo do monitoramento --}}
	<div class="row">
	
		<!-- Resumo do satélite -->
		<div class="col-5">
			<h3>Resumo:</h3>
			<ul>
				@isset($tv)
					<li>Canais de TV ( H264 + H265/HEVC ): {{$tv}}</li>
				@endisset
				@isset($tv)
					<li>
						<span class="bg-primary bg-opacity-10">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span>
						Canais de TV H265/HEVC: {{$hevc}}
					</li>
				@endisset
				@isset($radio)
					<li>
						<span class="bg-success bg-opacity-10">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span>
						Canais de rádio: {{$radio}}
					</li>
				@endisset
				@isset($b5)
					<li>
						Canais do B5 : {{$b5}}
					</li>
				@endisset
				@isset($b6)
					<li>
						Canais do B6 ( H265/HEVC ): {{$b6}}
					</li>
				@endisset
				@isset($logs[0])
					<li class="bg-danger bg-opacity-10">
						Última alteração: 
						dia 
						{{\Carbon\Carbon::parse($logs[0]->created_at)->format('d/m/Y')}}
						às 
						{{\Carbon\Carbon::parse($logs[0]->created_at)->format('h:i')}}
					</li>
				@endisset
			</ul>
		</div>

		<!-- Lista de redes -->
		<div class="col-3">
			<h3>Redes:</h3>
			@isset ( $networks )
				<ul>
				@foreach ( $networks as $net )
					@if ( $net->bouquet_name != "" )
						<li id="{{ $net->bouquet_name }}">
							{{ $net->bouquet_name }} = {{ $net->total }}
						</li>
					@endif
				@endforeach
				</ul>
			@endisset
		</div>

		<!-- Filtro busca por data -->
		<div class="col-3">
			<form action="lista/find" method="post">
				@csrf
				<div class="input-group">
  					<input name="inicio" id="inicio" type="date" class="form-control" value="{{\Carbon\Carbon::parse($inicio)->format('Y-m-d')}}"/>
					<button class="btn btn-outline-secondary" type="submit">Filtrar</button>
				</div>
			</form>

			<br><hr>
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" id="detailSwitch">
				<label class="form-check-label" for="detailSwitch">Lista detalhada</label>
			</div>
		</div>
	</div>

	<!-- Lista de logs -->
	@isset($logs[0])
	<div class="section">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0">
					<button
						class="btn btn-link collapsed text-decoration-none" data-bs-toggle="collapse" 
						data-bs-target="#log_list"  
						aria-expanded="false" 
						aria-controls="log_list">
					Histórico de alterações
				</h5>
			</div>

			<div id="log_list" class="collapse">
				<div class="card-body">

					<table class="table table-striped table-hover">
						<tbody>
							@foreach($logs as $log)
								<tr>
									<td>{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}}</td>
									<td>{{$log->name}}</td>
									<td>{{$log->description}}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	@endisset

	<!-- Lista de transponders -->
	@isset($transponders)
		@foreach($transponders as $transponder) 
			<x-transponder-new :transponder="$transponder" />
		@endforeach
	@endisset

	<br><hr><br>
	<h2>Lista de canais detalhada</h2>
	@isset($transponders)
		@foreach($transponders as $transponder) 
			<x-transponder :transponder="$transponder" />
			@endforeach
	@endisset 

</div> 

<?php
	//echo '<pre>'; 
	//print_r($logs); 
	//echo '</pre>' ;	
?>
@endsection

@section('script-commands')
<script type="text/javascript"> 

	const CSS_COLOR_NAMES = ["AntiqueWhite","Chartreuse","Gainsboro","Gold","GoldenRod","Gray","Grey","Green","GreenYellow","HoneyDew","HotPink","IndianRed","Indigo","Ivory","Khaki","Lavender","LavenderBlush","LawnGreen","LemonChiffon","LightBlue","LightCoral","LightCyan","LightGoldenRodYellow","LightGray","LightGrey","LightGreen","LightPink","LightSalmon","LightSeaGreen","LightSkyBlue","LightSlateGray","LightSlateGrey","LightSteelBlue","LightYellow","Lime","LimeGreen","Linen","Magenta","Maroon","MediumAquaMarine","MediumBlue","MediumOrchid","MediumPurple","MediumSeaGreen","MediumSlateBlue","MediumSpringGreen","MediumTurquoise","MediumVioletRed","MidnightBlue","MintCream","MistyRose","Moccasin","NavajoWhite","Navy","OldLace","Olive","OliveDrab","Orange","OrangeRed","Orchid","PaleGoldenRod","PaleGreen","PaleTurquoise","PaleVioletRed","PapayaWhip","PeachPuff","Peru","Pink","Plum","PowderBlue","Purple","RebeccaPurple","Red","RosyBrown","RoyalBlue","SaddleBrown","Salmon","SandyBrown","SeaGreen","SeaShell","Sienna","Silver","SkyBlue","SlateBlue","SlateGray","SlateGrey","Snow","SpringGreen","SteelBlue","Tan","Teal","Thistle","Tomato","Turquoise","Violet","Wheat","White","WhiteSmoke","Yellow","YellowGreen"];

	/** Função será executada após a página ser carregada */
	$(document).ready(function(){

		var ndx = 0 ;
		var nets = <?php echo $nets ?>;
		console.log(nets);
		$.each(nets, function(value,index) {
			console.log(index['bouquet_name']);

			var cl = index['bouquet_name'] ;
			var cor = CSS_COLOR_NAMES[ndx++];
			$('.'+cl).each(function(){
				console.log('globo');
				$(this).css({'backgroundColor':cor});
				$(this).closest('.service-box').css({'backgroundColor':cor});
			});
			$('#'+cl).css({'backgroundColor':cor});
		});

		// Oculta detalhes
		$('.detailed-data').hide('slow');
	});

	$('#detailSwitch').on('change', function(){
		if ($(this).is(':checked')) {
			$('.detailed-data').show('slow');
		}
  		else {
			$('.detailed-data').hide('slow');
		}
	});

</script>
@endsection