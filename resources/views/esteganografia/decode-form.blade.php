@extends('layouts.main-layout')

@section('title', 'Decodificar')

@section('content')
    <div class="card mt-5">
        <div class="card-header pt-2 m-0">
            <p class="text-center m-0">Decodifique a mensagem escondida em sua imagem</p>
        </div>
        <div class="card-body">
            <form action="{{ route('decode-submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label for="file" class="form-label">Selecione uma imagem</label>
                    <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file">
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>

        </div>

    </div>
@endsection
