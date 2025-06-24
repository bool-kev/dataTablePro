<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'DataTable Pro - Authentification' }}</title>
    <meta name="description" content="Connectez-vous à DataTable Pro pour accéder à votre plateforme d'analyse de données collaborative.">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('index.css')}}" rel="stylesheet">
    
    @include('partials.head')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <svg class="logo" viewBox="0 0 40 40" fill="none">
                    <rect width="40" height="40" rx="8" fill="url(#gradient)"/>
                    <path d="M12 16h16M12 20h12M12 24h8" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="gradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#2684FF"/>
                            <stop offset="100%" stop-color="#0052CC"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span class="brand-text">DataTable Pro</span>
            </div>
            <div class="nav-links">
                <a href="{{ route('home') }}#features">Fonctionnalités</a>
                <a href="{{ route('home') }}#demo">Démo</a>
                <a href="{{ route('home') }}#pricing">Tarifs</a>
                <a href="{{ route('home') }}#contact">Contact</a>
                @if (request()->routeIs('login'))
                    <button class="btn-secondary" onclick="window.location.href='{{ route('register') }}'">S'inscrire</button>
                    <button class="btn-primary current">Connexion</button>
                @else
                    <button class="btn-secondary current">Inscription</button>
                    <button class="btn-primary" onclick="window.location.href='{{ route('login') }}'">Connexion</button>
                @endif
            </div>
            <button class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>    <!-- Main Content -->
    <main class="auth-main">
        <div class="container">
            <div class="auth-wrapper">
                <!-- Contenu du formulaire -->
                <div class="auth-form-section">
                    <div class="auth-form-content">
                        <!-- Logo et titre intégrés -->
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">📊</div>
                            <h2 style="font-size: 1.5rem; font-weight: 700; color: #2684FF; margin-bottom: 0; line-height: 1.2;">DataTable Pro</h2>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
                
                <!-- Section décorative -->
                <div class="auth-visual-section">
                    <div class="auth-visual-content">
                        <div class="auth-visual-icon">📊</div>
                        <h2 class="auth-visual-title">DataTable Pro</h2>
                        <p class="auth-visual-subtitle">
                            @if (request()->routeIs('login'))
                                Bon retour ! Accédez à votre workspace et continuez votre analyse de données.
                            @else
                                Rejoignez des milliers d'utilisateurs qui font confiance à DataTable Pro pour leurs analyses.
                            @endif
                        </p>
                        <div class="auth-visual-stats">
                            <div class="auth-stat">
                                <span class="auth-stat-number">50K+</span>
                                <span class="auth-stat-label">Fichiers Traités</span>
                            </div>
                            <div class="auth-stat">
                                <span class="auth-stat-number">99.9%</span>
                                <span class="auth-stat-label">Uptime</span>
                            </div>
                            <div class="auth-stat">
                                <span class="auth-stat-number">2M+</span>
                                <span class="auth-stat-label">Lignes Analysées</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <svg class="logo" viewBox="0 0 40 40" fill="none">
                            <rect width="40" height="40" rx="8" fill="url(#footerGradient)"/>
                            <path d="M12 16h16M12 20h12M12 24h8" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <defs>
                                <linearGradient id="footerGradient" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#2684FF"/>
                                    <stop offset="100%" stop-color="#0052CC"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <span>DataTable Pro</span>
                    </div>
                    <p>La plateforme d'analyse de données collaborative qui transforme votre façon de travailler avec les données.</p>
                </div>
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Produit</h4>
                        <a href="{{ route('home') }}#features">Fonctionnalités</a>
                        <a href="{{ route('home') }}#pricing">Tarifs</a>
                        <a href="#">Sécurité</a>
                        <a href="#">API</a>
                    </div>
                    <div class="footer-column">
                        <h4>Ressources</h4>
                        <a href="#">Documentation</a>
                        <a href="#">Tutoriels</a>
                        <a href="#">Blog</a>
                        <a href="#">Centre d'aide</a>
                    </div>
                    <div class="footer-column">
                        <h4>Entreprise</h4>
                        <a href="#">À propos</a>
                        <a href="#">Carrières</a>
                        <a href="#">Presse</a>
                        <a href="{{ route('home') }}#contact">Contact</a>
                    </div>
                    <div class="footer-column">
                        <h4>Légal</h4>
                        <a href="#">Confidentialité</a>
                        <a href="#">Conditions</a>
                        <a href="#">RGPD</a>
                        <a href="#">Cookies</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 DataTable Pro. Tous droits réservés.</p>
                <div class="social-links">
                    <a href="#" aria-label="Twitter">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/>
                            <circle cx="4" cy="4" r="2"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="GitHub">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <div class="mobile-menu-content">
            <a href="{{ route('home') }}#features">Fonctionnalités</a>
            <a href="{{ route('home') }}#demo">Démo</a>
            <a href="{{ route('home') }}#pricing">Tarifs</a>
            <a href="{{ route('home') }}#contact">Contact</a>
            @if (request()->routeIs('login'))
                <button class="btn-secondary btn-full" onclick="window.location.href='{{ route('register') }}'">S'inscrire</button>
                <button class="btn-primary btn-full current">Connexion</button>
            @else
                <button class="btn-secondary btn-full current">Inscription</button>
                <button class="btn-primary btn-full" onclick="window.location.href='{{ route('login') }}'">Connexion</button>
            @endif
        </div>
    </div>

    <script src="{{ asset('index.js') }}"></script>
    @fluxScripts
</body>
</html>
