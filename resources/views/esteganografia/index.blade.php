@extends('layouts.main-layout')

@section('title', 'Codificar')

@section('content')
    <div class="card mt-5">
        <div class="card-header pt-2 m-0">
            <p class="text-center m-0">Crie uma imagem com um texto escondido</p>
        </div>
        <div class="card-body">
            <form action="{{ route('encode') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label for="file" class="form-label">Selecione uma imagem</label>
                    <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file">
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="text" class="form-label">Escreva o texto a ser escondido</label>
                    <textarea class="form-control @error('text') is-invalid @enderror" id="text" name="text" rows="3">{{ old('text') }}</textarea>
                    @error('text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>

        </div>

    </div>
@endsection
