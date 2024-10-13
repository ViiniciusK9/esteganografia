@extends('layouts.main-layout')

@section('title', 'Decodificar')

@section('content')
    <div class="card mt-5">
        <div class="card-header pt-2 m-0">
            <p class="text-center m-0">Texto da sua imagem</p>
        </div>
        <div class="card-body">
            
            <div class="mb-3">
                <label for="message" class="form-label">Mensagem decodificada</label>
                <textarea class="form-control" name="message" id="message" disabled>{{ $decodeMessage }}</textarea>
            </div>

        </div>

    </div>
@endsection
