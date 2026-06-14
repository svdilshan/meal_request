<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Meal Request') - Meal Request System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Theme Styling -->
    <style>
        :root {
            --app-blue: #003087;
            --app-blue-dark: #001f5c;
            --app-blue-light: #e6ebf5;
            --app-red: #E30613;
            --app-red-dark: #b8050f;
            --app-red-light: #fdedee;
            --bg-primary: #F5F5F5;
            --card-bg: #FFFFFF;
            --text-primary: #222222;
            --text-muted: #6C757D;
        }

        body {
            font-family: 'Roboto', 'Outfit', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
        }

        /* Premium Navbar */
        .app-navbar {
            background-color: var(--app-blue);
            border-bottom: 4px solid var(--app-red);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 1.4rem;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 4px;
            transition: all 0.2s ease-in-out;
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .nav-link:hover, .nav-link.active {
            color: #FFFFFF !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Premium Cards */
        .app-card {
            background: var(--card-bg);
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 48, 135, 0.04);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .app-card:hover {
            box-shadow: 0 12px 30px rgba(0, 48, 135, 0.08);
        }

        .app-card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding: 1.25rem 1.5rem;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--app-blue);
            border-color: var(--app-blue);
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s ease;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--app-blue-dark);
            border-color: var(--app-blue-dark);
            transform: translateY(-1px);
        }

        .btn-accent {
            background-color: var(--app-red);
            border-color: var(--app-red);
            color: #FFFFFF;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s ease;
        }

        .btn-accent:hover {
            background-color: var(--app-red-dark);
            border-color: var(--app-red-dark);
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .badge-role {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.35em 0.65em;
        }

        /* Footer styling */
        footer {
            margin-top: auto;
            background-color: #FFFFFF;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem 0;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Custom notifications */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 320px;
            animation: slideIn 0.3s ease-out forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('request.index') }}">
                <span class="text-white fw-bold">Meal</span>
                <span class="text-white-50 ms-1 fw-light">Lanka</span>
                <span class="badge bg-danger ms-2" style="font-size: 0.65rem;">MEALS</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="appNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <i class="bi bi-people me-1"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
                                </a>
                            </li>
                            @if(auth()->user()->isSuperAdmin())
                                <li class="nav-item">
                                    <a class="nav-link {{ Route::is('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                                        <i class="bi bi-gear me-1"></i> Settings
                                    </a>
                                </li>
                            @endif
                        @endif
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('request.*') ? 'active' : '' }}" href="{{ route('request.index') }}">
                                <i class="bi bi-journal-plus me-1"></i> Request Form
                            </a>
                        </li>
                    @endauth
                </ul>
                
                @auth
                    <div class="d-flex align-items-center text-white">
                        <div class="me-3 text-end d-none d-sm-block">
                            <div class="fw-bold text-white">{{ auth()->user()->name }}</div>
                            <small class="text-white-50">
                                EPF: {{ auth()->user()->epf_no }} |
                                <span class="badge bg-light text-dark badge-role">
                                    {{ str_replace('_', ' ', auth()->user()->role) }}
                                </span>
                            </small>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm px-3">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Custom Toasts for Success/Errors -->
    @if(session('success'))
        <div class="notification-toast alert alert-success alert-dismissible fade show shadow-lg border-start border-success border-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                <div>
                    <h6 class="alert-heading mb-0 fw-bold">Success</h6>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any() && !Route::is('login'))
        <div class="notification-toast alert alert-danger alert-dismissible fade show shadow-lg border-start border-danger border-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-3"></i>
                <div>
                    <h6 class="alert-heading mb-0 fw-bold">Notification</h6>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Content Area -->
    <main class="py-5">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Meal Request System. All rights reserved.</p>
            <small class="text-white-50 bg-secondary px-2 py-1 rounded" style="font-size:0.7rem;">Meal Request System v1.0</small>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss custom toasts after 5 seconds
        const toasts = document.querySelectorAll('.notification-toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(toast);
                bsAlert.close();
            }, 6000);
        });
    </script>
    @yield('scripts')
</body>
</html>
