<!DOCTYPE html>
<html>
<head>
    <title>Головна</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    @auth
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1>Ласкаво просимо!</h1>
                <p>Ви увійшли як <strong>{{ auth()->user()->name }}</strong></p>

                @if(auth()->user()->hasRole('admin'))
                    <a href="/admin/categories" class="btn btn-primary btn-lg mb-3">Адмін-панель</a>
                @endif

                <div class="mt-4">
                    <form action="/logout" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">Вийти</button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <h1 class="mb-4">Новини</h1>
                <p class="lead mb-4">Ласкаво просимо!</p>
                <a href="/login" class="btn btn-primary btn-lg">Увійти</a>
            </div>
        </div>
    @endauth
</div>
</body>
</html>
