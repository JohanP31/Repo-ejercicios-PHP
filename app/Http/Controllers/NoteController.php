<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(array_keys(Note::CATEGORIES))],
        ]);

        $notes = Note::query()
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($validated['category'] ?? null, function (Builder $query, string $category): void {
                $query->where('category', $category);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('notes.index', [
            'notes' => $notes,
            'categories' => Note::CATEGORIES,
            'filters' => $validated,
        ]);
    }

    public function create(): View
    {
        return view('notes.create', [
            'categories' => Note::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Note::query()->create($this->validatedNote($request));

        return redirect()->route('notes.index')->with('message', [
            'content' => 'Nota guardada. Ahora es visible para todos.',
            'type' => 'success',
        ]);
    }

    public function edit(Note $note): View
    {
        return view('notes.edit', [
            'note' => $note,
            'categories' => Note::CATEGORIES,
        ]);
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $note->update($this->validatedNote($request));

        return redirect()->route('notes.index')->with('message', [
            'content' => 'Nota actualizada para todos.',
            'type' => 'success',
        ]);
    }

    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();

        return redirect()->route('notes.index')->with('message', [
            'content' => 'Nota eliminada.',
            'type' => 'success',
        ]);
    }

    /**
     * @return array{title: string, content: string, category: string}
     */
    private function validatedNote(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:10000'],
            'category' => ['required', Rule::in(array_keys(Note::CATEGORIES))],
        ], [
            'title.required' => 'Escribe un título para la nota.',
            'title.max' => 'El título no puede superar los 120 caracteres.',
            'content.required' => 'Escribe el contenido de la nota.',
            'content.max' => 'El contenido no puede superar los 10 000 caracteres.',
            'category.required' => 'Selecciona una categoría.',
            'category.in' => 'Selecciona una categoría válida.',
        ]);
    }
}
