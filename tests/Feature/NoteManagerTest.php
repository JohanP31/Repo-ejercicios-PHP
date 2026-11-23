<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_notes_create_and_search(): void
    {
        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee('Notas compartidas')
            ->assertSee('crea notas para eventos importantes o recordatorios');

        $this->post(route('notes.store'), [
            'title' => 'Plan semanal',
            'content' => 'Preparar reunión de proyecto',
            'category' => 'work',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'title' => 'Plan semanal',
            'content' => 'Preparar reunión de proyecto',
            'category' => 'work',
        ]);

        $this->get(route('notes.index', [
            'search' => 'reunión',
            'category' => 'work',
        ]))
            ->assertOk()
            ->assertSee('Plan semanal')
            ->assertDontSee('Idea de viaje');
    }

    public function test_note_edit_and_delete(): void
    {
        $note = Note::query()->create([
            'title' => 'Borrador',
            'content' => 'Contenido inicial',
            'category' => 'personal',
        ]);

        $this->get(route('notes.edit', $note))
            ->assertOk()
            ->assertSee('Contenido inicial');

        $this->put(route('notes.update', $note), [
            'title' => 'Nota final',
            'content' => 'Contenido actualizado',
            'category' => 'study',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Nota final',
            'content' => 'Contenido actualizado',
            'category' => 'study',
        ]);

        $this->delete(route('notes.destroy', $note))
            ->assertRedirect(route('notes.index'));

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_note_validation(): void
    {
        $this->from(route('notes.create'))
            ->post(route('notes.store'), [
                'title' => '',
                'content' => '',
                'category' => 'unknown',
            ])
            ->assertRedirect(route('notes.create'))
            ->assertSessionHasErrors(['title', 'content', 'category']);

        $this->assertDatabaseCount('notes', 0);
    }

    public function test_note_escaping(): void
    {
        $title = "<script>alert('title')</script>";
        $content = '<img src=x onerror=alert(1)>';

        $this->post(route('notes.store'), [
            'title' => $title,
            'content' => $content,
            'category' => 'other',
        ])->assertRedirect(route('notes.index'));

        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee($title)
            ->assertSee($content)
            ->assertDontSee($title, false)
            ->assertDontSee($content, false);
    }

    public function test_note_filter_validation(): void
    {
        $this->get(route('notes.index', ['search' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('search');

        $this->get(route('notes.index', ['category' => 'private']))
            ->assertSessionHasErrors('category');
    }
}
