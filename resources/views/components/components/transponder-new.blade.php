<br><hr>
<div class="container" style="max-width: 90rem;">
	<div class="row" style="font-size:1.6em;">
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
	</div>

	<!-- Lista de alterações -->
	<x-components.log :logs="$transponder->alteracoes"/>

	<!-- Lista de serviços  -->
	<x-components.service-new :services="$transponder->services" />

</div>