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

    </ul>

</aside>
