@extends('layouts.main-layout')

@section('title', 'Comparar')

@section('content')

    <div class="container-fluid mt-5">
        <div class="row justify-content-evenly">
            <div class="col-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Imagem Original</h2>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <img class="img-fluid" src="{{ asset('storage/' . $image->image_path) }}" alt="Imagem Original">
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Imagem Modificada</h2>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <img class="img-fluid" src="{{ asset('storage/' . $image->modified_image_path) }}" alt="Imagem Modificada">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection