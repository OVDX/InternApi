@extends('admin.layout')

@section('title', 'Категорії')
@section('header', 'Категорії')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Список категорій</h4>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            Додати категорію
        </a>
    </div>

    @if($categories->isEmpty())
        <div class="alert alert-info">
            Категорій поки що немає.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Назва (uk)</th>
                        <th>Назва (en)</th>
                        <th>Позиція</th>
                        <th>Статус</th>
                        <th class="text-end">Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->translate('uk')->name ?? '' }}</td>
                            <td>{{ $category->translate('en')->name ?? '' }}</td>
                            <td>{{ $category->position }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Активна</span>
                                @else
                                    <span class="badge bg-secondary">Неактивна</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                    Редагувати
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Точно видалити категорію?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        Видалити
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $categories->links() }}
        </div>
    @endif
@endsection
