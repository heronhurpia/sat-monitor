<br><hr>
<div class="container" style="max-width: 90rem;">
	<div class="row  bg-secondary bg-opacity-10" style="font-size:1.6em; padding-top: 10px; padding-bottom: 10px;">
		<div class="col-4 {{$transponder->has_logs ? 'text-danger' : 'text-black'}}">
			@if ( $transponder->count_logs )
				<span class="badge rounded-pill bg-danger">{{$transponder->count_logs}}</span>
			@endif
			Frequencia: {{$transponder->frequency/1000}}MHz
		</div>
		<div class="col-4 {{$transponder->has_logs ? 'text-danger' : 'text-black'}}">
			S/R: {{$transponder->symbol_rate}}KBPs
		</div>
		<div class="col-4 {{$transponder->has_logs ? 'text-danger' : 'text-black'}}">
			Polaridade: {{$transponder->polarity}}
		</div>
		<div class="col-4 {{$transponder->has_logs ? 'text-danger' : 'text-black'}}">
			Modulação: {{$transponder->dvb_mode}}
		</div>
	</div>

	<!-- Lista de alterações -->
	<x-log :logs="$transponder->alteracoes"/>

	<!-- Lista de serviços  -->
	<br>
	<x-service-new :services="$transponder->services" />

</div>
