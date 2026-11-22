<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        return view('tasks.index', [
            'tasks' => Task::orderBy('completed')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ], [
            'title.required' => 'Escribe el nombre de la tarea.',
            'title.max' => 'La tarea no puede superar los 255 caracteres.',
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')->with('message', 'Tarea agregada.');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $task->update(['completed' => ! $task->completed]);

        return redirect()->route('tasks.index')->with('message', 'Estado de la tarea actualizado.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('message', 'Tarea eliminada.');
    }
}
