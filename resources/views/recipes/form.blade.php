<div class="card-body">
    @guest
        <div class="alert alert-info" role="status">
            Las recetas publicadas sin iniciar sesión serán públicas y no podrán editarse ni eliminarse después.
        </div>
    @endguest

    <div class="mb-3">
        <label for="title" class="form-label">Nombre de la receta</label>
        <input
            id="title"
            name="title"
            type="text"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $recipe->title) }}"
            maxlength="160"
            required
        >
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Descripción (opcional)</label>
        <textarea
            id="description"
            name="description"
            class="form-control @error('description') is-invalid @enderror"
            rows="2"
            maxlength="1000"
        >{{ old('description', $recipe->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="category" class="form-label">Tipo de comida</label>
            <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                <option value="">Selecciona un tipo</option>
                @foreach ($categories as $key => $category)
                    <option value="{{ $key }}" @selected(old('category', $recipe->category) === $key)>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
            @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="prep_time" class="form-label">Tiempo (minutos)</label>
            <input
                id="prep_time"
                name="prep_time"
                type="number"
                class="form-control @error('prep_time') is-invalid @enderror"
                value="{{ old('prep_time', $recipe->prep_time) }}"
                min="1"
                max="1440"
                required
            >
            @error('prep_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="servings" class="form-label">Porciones</label>
            <input
                id="servings"
                name="servings"
                type="number"
                class="form-control @error('servings') is-invalid @enderror"
                value="{{ old('servings', $recipe->servings) }}"
                min="1"
                max="100"
                required
            >
            @error('servings')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="ingredients" class="form-label">Ingredientes</label>
        <textarea
            id="ingredients"
            name="ingredients"
            class="form-control @error('ingredients') is-invalid @enderror"
            rows="6"
            maxlength="5000"
            placeholder="Escribe un ingrediente por línea"
            required
        >{{ old('ingredients', $recipe->ingredients) }}</textarea>
        <div class="form-text">Escribe una cantidad y un ingrediente por línea.</div>
        @error('ingredients')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="instructions" class="form-label">Preparación</label>
        <textarea
            id="instructions"
            name="instructions"
            class="form-control @error('instructions') is-invalid @enderror"
            rows="8"
            maxlength="10000"
            placeholder="Escribe un paso por línea"
            required
        >{{ old('instructions', $recipe->instructions) }}</textarea>
        @error('instructions')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>
