<form action="{{ $action }}" method="POST">
    @csrf
    @if ($note)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="title" class="form-label">Título</label>
        <input
            id="title"
            name="title"
            type="text"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $note?->title) }}"
            maxlength="120"
            required
        >
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="category" class="form-label">Categoría</label>
        <select
            id="category"
            name="category"
            class="form-select @error('category') is-invalid @enderror"
            required
        >
            <option value="">Selecciona una categoría</option>
            @foreach ($categories as $key => $category)
                <option value="{{ $key }}" @selected(old('category', $note?->category) === $key)>
                    {{ $category }}
                </option>
            @endforeach
        </select>
        @error('category')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="content" class="form-label">Contenido</label>
        <textarea
            id="content"
            name="content"
            class="form-control @error('content') is-invalid @enderror"
            rows="8"
            maxlength="10000"
            required
        >{{ old('content', $note?->content) }}</textarea>
        @error('content')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <p class="text-muted">Esta nota será pública y compartida con todos los visitantes.</p>

    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn btn-secondary" href="{{ route('notes.index') }}">Volver</a>
</form>
