@extends('layouts.app')
@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8">
				<div class="card">
					<div class="card-header"><h5>{{ __('Nova senha') }}</h5></div>

					<form action="{{ route('update-password') }}" method="POST">
						@csrf
						<div class="card-body">
							@if (session('status'))
								<div class="alert alert-success" role="alert">
									{{ session('status') }}
								</div>
							@elseif (session('error'))
								<div class="alert alert-danger" role="alert">
									{{ session('error') }}
								</div>
							@endif

							<div class="mb-3">
								<label for="oldPasswordInput" class="form-label">Senha atual</label>
								<input name="old_password" type="password" class="form-control @error('old_password') is-invalid @enderror" id="oldPasswordInput"
									placeholder="Senha atual">
								@error('old_password')
									<span class="text-danger">{{ $message }}</span>
								@enderror
							</div>
							<div class="mb-3">
								<label for="newPasswordInput" class="form-label">Nova senha</label>
								<input name="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" id="newPasswordInput"
									placeholder="Nova senha">
								@error('new_password')
									<span class="text-danger">{{ $message }}</span>
								@enderror
							</div>
							<div class="mb-3">
								<label for="confirmNewPasswordInput" class="form-label">Confirme nova senha</label>
								<input name="new_password_confirmation" type="password" class="form-control" id="confirmNewPasswordInput"
									placeholder="Confirme nova senha">
							</div>

						</div>

						<div class="card-footer">
							<button class="btn btn-success">Atualizar senha</button>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

