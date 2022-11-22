@extends('layouts.app',['title' => 'Nível de qualidade'])

@section('content')

<!-- Gráfico de qualidade -->
<div class="container"> 
	<div class="panel panel-default blue lighten-5">
		<br>
		<div class="row">
			<div class="col l12 m12 s12">
 				<div id="curve_chart" style="width: 100%; height: 500px"></div>
 			</div>
 		</div>
 	</div>
 </div>
 
 <!-- Checkbox com a lista de transponders -->
 <br><hr/>
 <div class="container">
	<div class="row" id="checkbox_id">
	</div>
</div>
<br><hr/>

<!-- Lista de transponders -->
<div class="container">
	<table class="table table-hover">
		<thead>
			<tr>
				<th>Frequencia</th>
				<th>S/R</th>
				<th>Modulação</th>
				<th>FEC</th>
				<th>Qualidade</th>
				<th>Sinal</th>
				<th>SNR</th>
				<th>BER</th>
				<!-- <th>Data</th> -->
			</tr>
		</thead>
		<tbody>
			@isset($transponders)
				@foreach($transponders as $t)
					<tr>
					<td>{{number_format($t->frequency,0,',','.')}}MHz</td>
					<td>{{number_format($t->symbol_rate,0,',','.')}}MS/s</td>
					<td>{{$t->modulation_type}}</td>
					<td>{{$t->fec_rate}}</td>
					<td>{{$t->quality}}%</td>
					<td>{{$t->strength}}%</td>
					<td>{{$t->snr}}</td>
					<td>{{$t->ber}}</td>
					<!-- <td>{{ \Carbon\Carbon::parse($t->datetime)->format('d/m/Y H:i:s')}}</td> -->
					<tr>
				@endforeach
			@endisset
		</tbody>
	</table>
</div>


@endsection

<!-- Não pode conter a função $(document).ready -->
@section('script-commands')
<script type="text/javascript"> 

	const CSS_COLOR_NAMES = ["AntiqueWhite","Aqua","Black","Blue","BlueViolet","Brown","BurlyWood","CadetBlue","Chartreuse","Chocolate","Coral","CornflowerBlue","Cornsilk","Crimson","Cyan","DarkBlue","DarkCyan","DarkGoldenRod","DarkGray","DarkGrey","DarkGreen","DarkKhaki","DarkMagenta","DarkOliveGreen","DarkOrange","DarkOrchid","DarkRed","DarkSalmon","DarkSeaGreen","DarkSlateBlue","DarkSlateGray","DarkSlateGrey","DarkTurquoise","DarkViolet","DeepPink","DeepSkyBlue","DimGray","DimGrey","DodgerBlue","FireBrick","FloralWhite","ForestGreen","Fuchsia","Gainsboro","GhostWhite","Gold","GoldenRod","Gray","Grey","Green","GreenYellow","HoneyDew","HotPink","IndianRed","Indigo","Ivory","Khaki","Lavender","LavenderBlush","LawnGreen","LemonChiffon","LightBlue","LightCoral","LightCyan","LightGoldenRodYellow","LightGray","LightGrey","LightGreen","LightPink","LightSalmon","LightSeaGreen","LightSkyBlue","LightSlateGray","LightSlateGrey","LightSteelBlue","LightYellow","Lime","LimeGreen","Linen","Magenta","Maroon","MediumAquaMarine","MediumBlue","MediumOrchid","MediumPurple","MediumSeaGreen","MediumSlateBlue","MediumSpringGreen","MediumTurquoise","MediumVioletRed","MidnightBlue","MintCream","MistyRose","Moccasin","NavajoWhite","Navy","OldLace","Olive","OliveDrab","Orange","OrangeRed","Orchid","PaleGoldenRod","PaleGreen","PaleTurquoise","PaleVioletRed","PapayaWhip","PeachPuff","Peru","Pink","Plum","PowderBlue","Purple","RebeccaPurple","Red","RosyBrown","RoyalBlue","SaddleBrown","Salmon","SandyBrown","SeaGreen","SeaShell","Sienna","Silver","SkyBlue","SlateBlue","SlateGray","SlateGrey","Snow","SpringGreen","SteelBlue","Tan","Teal","Thistle","Tomato","Turquoise","Violet","Wheat","White","WhiteSmoke","Yellow","YellowGreen"];

	/** Função será executada após a página ser carregada */
	$(document).ready(function(){

		/** Cria chackbox para todas as linhas iniciais */
		createCheckbox();

		// Load the Visualization API and the corechart package.
		google.charts.load('current', {'packages':['corechart']});

		// Set a callback to run when the Google Visualization API is loaded.
		google.charts.setOnLoadCallback(drawChart);
	});

	/** Cria um check box para cada transponder */
	function createCheckbox() {
		var values = <?php print_r($quality[0]->get_transponders_lock_grid) ?>;
		values[0].forEach(function(data,index) {
			var disabled = "" ;
			if ( index === 0 ) {
				disabled = "disabled" ;
			}

			var content =	"<div class='col-md-2'>" +
	 							"<input class='check' type='checkbox' value='" + data + "' checked " + disabled + ">" +
	 							"<label style='margin-left: .25rem'>" + data + " </label>" +
	 						"</div>" ;
		 	$("#checkbox_id").append(content);
		});
	}

	/** Lista todos os valores de qualidade com checkbox = true */
	function qualityValues(){

		var result = [] ;
		var quality = <?php print_r($quality[0]->get_transponders_lock_grid) ?>;
		quality.forEach(function(value,index) {
			var tmp = [] ;
			$('.check').each(function(ndx) {
		  		if ($(this).is(':checked')) {
					tmp.push(value[ndx]);
				}
			});
			result.push(tmp);
		});
		return result ;
	}

	$(document).on('change','.check',function() {
		drawChart();
	});

	/* Monta tabela de cores de acordo com colunas selecionadas */
	function updateColorData() {
		var color_data = [] ;
		$('.check').each(function(ndx) {
			if ( ndx ) {
				if ($(this).is(':checked')) {
					color_data.push(CSS_COLOR_NAMES[ndx]);
					//console.log(ndx + ": " + CSS_COLOR_NAMES[ndx]);
				}
			}
		});
		console.log(color_data);
		return color_data ;
	}

	/* Busca valores máximo e mínimo de qualidade */
	function getMaxMinValues() {

		/* Carrega a variável contendo todos os dados de qualidade */
		var quality = <?php print_r($quality[0]->get_transponders_lock_grid) ?>;
		var max = 0 ;
		var min = 99999 ;

		/* Busca em cada linha de dados */
		quality.forEach(function(value,index) {
			if ( index <= 3 ) {
				console.log(value);
			}
			/* Ignora primeira linha */
			if ( index ) {
				/** Busca em cada coluna */
				value.forEach(function(data,ndx){
					/** Ignora primeira coluna */
					if ( ndx && ( data != null ) ) {
						min = data < min ? data : min ;
						max = data > max ? data : max ;
					}
				});
			}
		});

		min = 10*(Math.floor(min/10)) ;
		max = 10*(Math.ceil(max/10)) ; 
		console.log( min + ' - ' + max ) ;
	
    	return {
        	max: max,
      	min: min
   	};
	}

	/** Desenha gráfico na tela */
	function drawChart() {
		
		/** Cria gráficos */
		var quality = qualityValues();
		var data = google.visualization.arrayToDataTable(quality);

		/* Monta tabela de cores de acordo com colunas selecionadas */
		var color_data = updateColorData();

		/* Busca valores máximo e mínimo de qualidade N71000846619 V79469480900 */
		var values = getMaxMinValues();

		/** Monta lista de opções */
		var options = {
			title: 'Qualidade da recepção',
			curveType: 'function',
			legend: { 
				position: 'bottom' 
			},
			hAxis: {
      		gridlines: { 
					count: 10
				}
    		},
			vAxis: {
				viewWindow: {
					'max': values.max,
					'min': values.min
				}
			},
			colors: color_data 
		};

		var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
		chart.draw(data, options);
	}
</script>
@endsection
