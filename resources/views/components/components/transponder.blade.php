<div class="section">
	<div class="card">
		<div class="card-header">
			<h5 class="mb-0">
				<button
				class="btn {{$transponder->has_logs ? 'btn-link' : 'btl-flat'}} collapsed text-decoration-none" 
					data-bs-toggle="collapse" 
					data-bs-target="{{'#id_' . $transponder->frequency}}"  
					aria-expanded="false" 
					aria-controls="{{'id_' . $transponder->frequency}}"
					style:{font-}></style>
				Frequencia = {{$transponder->frequency}}MHz - S/R = {{$transponder->symbol_rate}}KBPs, {{$transponder->polarity}}
			</h5>
		</div>

		<div id="{{'id_' . $transponder->frequency}}" class="collapse">
			<div class="card-body">
				@isset($transponder->alteracoes)
					<ul>
					@foreach($transponder->alteracoes as $log)
						<li>{{\Carbon\Carbon::parse($log->created_at)->format('d/m/Y h:i')}} - {{ $log->description}}, alterado à {{ $log->last }} minutos</li> 
					@endforeach
					</ul>
				@endisset

				@isset($transponder->services)
					@foreach($transponder->services as $service) 
						<x-components.service :service="$service" />
					@endforeach
				@endisset
			</div>
		</div>
	</div>
</div>
