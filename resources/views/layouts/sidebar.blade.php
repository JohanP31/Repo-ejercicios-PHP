<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link " href="index.html">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('sections.index') }}">
                <i class="bi bi-person"></i>
                <span>Secciones</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tasks.*') ? '' : 'collapsed' }}" href="{{ route('tasks.index') }}">
                <i class="bi bi-check2-square"></i>
                <span>Lista de tareas</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tips.*') ? '' : 'collapsed' }}" href="{{ route('tips.index') }}">
                <i class="bi bi-cash-coin"></i>
                <span>Calculadora de propinas</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('passwords.*') ? '' : 'collapsed' }}" href="{{ route('passwords.index') }}">
                <i class="bi bi-shield-lock"></i>
                <span>Generador de contraseñas</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('expenses.*') ? '' : 'collapsed' }}" href="{{ route('expenses.index') }}">
                <i class="bi bi-wallet2"></i>
                <span>Gestor de gastos</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reservations.*') ? '' : 'collapsed' }}" href="{{ route('reservations.index') }}">
                <i class="bi bi-calendar-check"></i>
                <span>Reservar cita</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('notes.*') ? '' : 'collapsed' }}" href="{{ route('notes.index') }}">
                <i class="bi bi-journal-text"></i>
                <span>Gestor de notas</span>
            </a>
        </li>

        @auth
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('calendar.*') ? '' : 'collapsed' }}" href="{{ route('calendar.index') }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Calendario de eventos</span>
                </a>
            </li>
        @endauth

    </ul>

</aside>
