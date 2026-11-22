<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskListTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_list_is_available_without_authentication(): void
    {
        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Lista de tareas');
    }

    public function test_guest_can_create_complete_and_delete_a_task(): void
    {
        $this->post(route('tasks.store'), ['title' => 'Leer'])
            ->assertRedirect(route('tasks.index'));

        $task = Task::firstOrFail();
        $this->assertFalse($task->completed);

        $this->patch(route('tasks.toggle', $task))
            ->assertRedirect(route('tasks.index'));

        $this->assertTrue($task->fresh()->completed);

        $this->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_title_is_required(): void
    {
        $this->from(route('tasks.index'))
            ->post(route('tasks.store'), ['title' => ''])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('tasks', 0);
    }
}
