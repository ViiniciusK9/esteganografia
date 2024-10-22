@extends('layouts.main-layout')

@section('title', 'Listar')

@section('content')

    <div class="container-fluid mt-5">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Comparar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($images as $image)
                    <tr>
                        <td>{{ $image->id }}</td>
                        <td>
                            <img src="{{ asset('storage/' . $image->image_path) }}" width="30" height="auto">
                        </td>
                        <td>{{ $image->image_path }}</td>
                        <td>
                            <a href="{{ route('show', $image->id) }}" class="btn btn-outline-primary">Visualizar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="4">Nada aqui</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
