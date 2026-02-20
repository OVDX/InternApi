@extends('admin.layout')

@section('title', 'Створити категорію')
@section('header', 'Створити категорію')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Позиція</label>
                    <input type="number" name="position" class="form-control" value="{{ old('position', 0) }}" min="0" required>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Активна</label>
                </div>

                <hr>

                <h5>Переклади</h5>

                <div class="mb-3 mt-3">
                    <h6>Українська (uk)</h6>
                    <label class="form-label">Назва</label>
                    <input type="text" name="translations[uk][name]" class="form-control"
                           value="{{ old('translations.uk.name') }}" required>
                    <label class="form-label mt-2">Опис</label>
                    <textarea name="translations[uk][description]" class="form-control" rows="2">{{ old('translations.uk.description') }}</textarea>
                </div>

                <div class="mb-3 mt-3">
                    <h6>Англійська (en)</h6>
                    <label class="form-label">Назва</label>
                    <input type="text" name="translations[en][name]" class="form-control"
                           value="{{ old('translations.en.name') }}" required>
                    <label class="form-label mt-2">Опис</label>
                    <textarea name="translations[en][description]" class="form-control" rows="2">{{ old('translations.en.description') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Зберегти
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    Назад
                </a>
            </form>
        </div>
    </div>
@endsection
