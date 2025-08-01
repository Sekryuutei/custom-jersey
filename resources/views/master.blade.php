<!DOCTYPE html>
<html lang="en" class="h-100">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" /> 
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"/>
        <title>Kustomisasi Jersey</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{asset('assets/favicon.ico')}}" />
        <!-- Custom Google font-->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@100;200;300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link rel="stylesheet" type="text/css" href="{{ asset('/css/styles.css') }}">
        </head>
    <body class="d-flex flex-column h-100">
        <main class="flex-shrink-0">
            <!-- Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
                <div class="container px-5">
                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <span class="navbar-brand"><span class="fw-bolder text-primary">Oceana Corporation</span></span>
                    @else
                        <a class="navbar-brand" href="/"><span class="fw-bolder text-primary">Oceana Corporation</span></a>
                    @endif
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 small fw-bolder">
                            @if(Auth::guest() || Auth::user()->role !== 'admin')
                                <li class="nav-item"><a class="nav-link" href="{{ route('tutor') }}">Panduan</a></li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('cart.index') }}">
                                        Keranjang
                                        @if(isset($cartItemCount) && $cartItemCount > 0)
                                            <span class="badge bg-danger rounded-pill">{{ $cartItemCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                            @guest
                                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                            @else
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ Auth::user()->name }}
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        @if(Auth::user()->role !== 'admin')
                                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">Pesanan Saya</a></li>
                                        @endif
                                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                        <li><hr class="dropdown-divider" /></li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                                    Logout
                                                </a>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Page Content-->
            <div class="container px-5">
                @if(session('success'))
                    <div class="alert alert-success mt-3" role="alert">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger mt-3" role="alert">{{ session('error') }}</div>
                @endif
            </div>
            @yield('content')
        </main>
        <!-- Footer-->
        <footer class="bg-white py-4 mt-auto">
            <div class="container px-5">
                <div class="container px-5">
                    <div class="row gx-5 justify-content-center">
                        <div class="col-xxl-8">
                            <div class="text-center my-5">
                                <h2 class="fw-bolder fs-4"><span class="text-gradient d-inline">Tentang Saya</span></h2><br>
                                <a href="https://www.facebook.com/anggiez99" class="text-decoration-none text-dark"><img src="{{asset('assets/facebook.png')}}" width="50" height="50"></a>
                                <a href="https://www.instagram.com/anggiez9" class="text-decoration-none text-dark"><img src="{{asset('assets/instagram.png')}}" width="50" height="50"></a>
                                <a href="https://www.linkedin.com/in/anggie-abdurochman" class="text-decoration-none text-dark"><img src="{{asset('assets/linkedin.png')}}" width="50" height="50"></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center justify-content-between flex-column flex-sm-row">
                    <div class="col-auto"><div class="small m-0">@ Kustomisasi Jersey.</div></div>
                </div>
            </div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('/js/scripts.js') }}"></script>
    </body>
</html>
