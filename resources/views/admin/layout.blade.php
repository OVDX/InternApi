<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 220px;
            background-color: #343a40;
            color: #fff;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .content { flex: 1; padding: 20px; }
        .navbar-admin {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <aside class="sidebar">
        <div class="p-3 border-bottom border-secondary">
            <h5 class="mb-0">Admin Panel</h5>
        </div>
        <nav class="mt-2">
            <a href="{{ route('admin.categories.index') }}"
               class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                Категорії
            </a>
            <a href="{{ url('/') }}">На сайт</a>
        </nav>
    </aside>

    <div class="content">
        <div class="navbar-admin d-flex justify-content-between align-items-center">
            <div>
                @yield('header', 'Адмінка')
            </div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                   data-bs-toggle="dropdown">
                    <strong>{{ auth()->user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form action="/logout" method="POST" class="m-0">
                            @csrf
                            <button class="dropdown-item" type="submit">Вийти</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>



        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
