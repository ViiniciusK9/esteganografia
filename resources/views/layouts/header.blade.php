
<div class="container-fluid p-0 bg-dark">
    <nav class="container navbar navbar-expand-lg bg-dark " data-bs-theme="dark">
        <div class="container-fluid">

            <a class="navbar-brand" href="{{ route('index') }}">
                <i class="bi bi-image-fill me-2"></i>
                Esteganografia
            </a>


            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('index') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('index') }}">Codificar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('decode-form') ? 'active' : '' }}"
                            href="{{ route('decode-form') }}">Decodificar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('show') ? 'active' : '' }}"
                            href="{{ route('show') }}">Vizualizar lado a lado</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>
