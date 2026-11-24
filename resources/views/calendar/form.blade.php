<div class="card-body">
    <div class="mb-3">
        <label for="title" class="form-label">Título</label>
        <input
            id="title"
            name="title"
            type="text"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $event->title) }}"
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
            rows="4"
            maxlength="5000"
        >{{ old('description', $event->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="starts_at" class="form-label">Fecha y hora de inicio</label>
            <input
                id="starts_at"
                name="starts_at"
                type="datetime-local"
                class="form-control @error('starts_at') is-invalid @enderror"
                value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}"
                required
            >
            @error('starts_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="ends_at" class="form-label">Fecha y hora de finalización</label>
            <input
                id="ends_at"
                name="ends_at"
                type="datetime-local"
                class="form-control @error('ends_at') is-invalid @enderror"
                value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}"
                required
            >
            @error('ends_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-3 mb-4">
        <label for="reminder_minutes" class="form-label">Avisarme</label>
        <select
            id="reminder_minutes"
            name="reminder_minutes"
            class="form-select @error('reminder_minutes') is-invalid @enderror"
        >
            <option value="">Sin recordatorio</option>
            @foreach ($reminderOptions as $minutes => $label)
                <option
                    value="{{ $minutes }}"
                    @selected((string) old('reminder_minutes', $event->reminder_minutes ?? '') === (string) $minutes)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text">El aviso aparecerá una vez dentro del calendario cuando lo abras.</div>
        @error('reminder_minutes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>
