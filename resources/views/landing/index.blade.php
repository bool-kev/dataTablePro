
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, maximum-scale=5.0">
    <title>DataTable Pro - Plateforme d'Analyse de Données Collaborative</title>
    <meta name="description" content="Importez, analysez et collaborez sur vos données avec DataTable Pro. Solution complète pour l'analyse de fichiers CSV et Excel en équipe.">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="{{ asset('index.css')}}" rel="stylesheet">
    <link href="{{ asset('css/app.css')}}" rel="stylesheet">
    @vite(['resources/css/mobile-landing.css'])
    
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    
    <!-- Theme Color for mobile browsers -->
    <meta name="theme-color" content="#2684FF">
    <meta name="msapplication-navbutton-color" content="#2684FF">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
</head>
<body>
    <!-- Skip to content link for accessibility -->
    <a href="#hero" class="skip-link">Aller au contenu principal</a>
    
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
                <a href="#features">Fonctionnalités</a>
                <a href="#demo">Démo</a>
                <a href="#pricing">Tarifs</a>
                <a href="#contact">Contact</a>
                <button class="btn-secondary" onclick="window.location.href='{{ route('login') }}'">Connexion</button>
                <button class="btn-primary" onclick="window.location.href='{{ route('register') }}'">Essai Gratuit</button>
            </div>
            <button class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="hero-background"></div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Transformez vos <span class="gradient-text">données</span> en insights puissants
                    </h1>
                    <p class="hero-subtitle">
                        DataTable Pro est la plateforme collaborative ultime pour importer, analyser et visualiser vos fichiers CSV et Excel. Conçue pour les équipes modernes qui veulent des résultats rapides.
                    </p>
                    <div class="hero-actions">
                        <button class="btn-primary btn-large" onclick="window.location.href='{{ route('register') }}'">
                            <span>Commencer Gratuitement</span>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                        <button class="btn-demo" onclick="scrollToDemo()">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <span>Voir la Démo</span>
                        </button>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <span class="stat-number">50K+</span>
                            <span class="stat-label">Fichiers Traités</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">99.9%</span>
                            <span class="stat-label">Uptime</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">2M+</span>
                            <span class="stat-label">Lignes Analysées</span>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="dashboard-preview">
                        <div class="dashboard-header">
                            <div class="dashboard-nav">
                                <div class="nav-dot red"></div>
                                <div class="nav-dot yellow"></div>
                                <div class="nav-dot green"></div>
                            </div>
                            <div class="dashboard-title">DataTable Pro</div>
                        </div>
                        <div class="dashboard-content">
                            <div class="table-header">
                                <div class="search-bar">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="m21 21-4.35-4.35"/>
                                    </svg>
                                    <span>Rechercher...</span>
                                </div>
                                <div class="table-actions">
                                    <button class="action-btn">Filtrer</button>
                                    <button class="action-btn">Exporter</button>
                                </div>
                            </div>
                            <div class="data-rows">
                                <div class="data-row header">
                                    <span>Nom</span>
                                    <span>Email</span>
                                    <span>Statut</span>
                                </div>
                                <div class="data-row">
                                    <span>Jean Dupont</span>
                                    <span>jean@example.com</span>
                                    <span class="status active">Actif</span>
                                </div>
                                <div class="data-row">
                                    <span>Marie Martin</span>
                                    <span>marie@example.com</span>
                                    <span class="status pending">En attente</span>
                                </div>
                                <div class="data-row">
                                    <span>Paul Durand</span>
                                    <span>paul@example.com</span>
                                    <span class="status active">Actif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Tout ce dont vous avez besoin pour analyser vos données</h2>
                <p>Une suite complète d'outils pour transformer vos fichiers bruts en insights actionnables</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <h3>Import Intelligent</h3>
                    <p>Glissez-déposez vos fichiers CSV et Excel. Notre IA détecte automatiquement la structure et valide vos données en temps réel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3>Recherche Avancée</h3>
                    <p>Filtres intelligents, recherche globale et tri multi-colonnes. Trouvez exactement ce que vous cherchez en quelques clics.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a1 1 0 01-1-1V9a1 1 0 011-1h1a2 2 0 100-4H4a1 1 0 01-1-1V4a1 1 0 011-1h3a1 1 0 011 1v1z"/>
                        </svg>
                    </div>
                    <h3>Collaboration</h3>
                    <p>Workspaces partagés, permissions granulaires et modifications en temps réel. Travaillez en équipe sur vos analyses.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3>Visualisations</h3>
                    <p>Graphiques dynamiques, tableaux de bord personnalisables et métriques en temps réel pour comprendre vos données.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3>Sécurité</h3>
                    <p>Chiffrement bout-en-bout, audit trails et conformité RGPD. Vos données sont protégées avec les plus hauts standards.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <h3>Export Flexible</h3>
                    <p>Exportez vos analyses en CSV, Excel, JSON ou PDF. Templates personnalisables et exports programmés disponibles.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="demo">
        <div class="container">
            <div class="demo-content">
                <div class="demo-text">
                    <h2>Voyez DataTable Pro en action</h2>
                    <p>Découvrez comment nos utilisateurs transforment leurs workflows de données et gagnent des heures chaque semaine.</p>
                    <ul class="demo-features">
                        <li>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Import en 3 clics seulement
                        </li>
                        <li>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Interface intuitive et moderne
                        </li>
                        <li>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Analyses en temps réel
                        </li>
                    </ul>
                    <button class="btn-primary">Planifier une Démo</button>
                </div>
                <div class="demo-video">
                    <div class="video-placeholder">
                        <svg width="80" height="80" fill="white" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <p>Démo Interactive - 2 min</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing">
        <div class="container">
            <div class="section-header">
                <h2>Tarifs simples et transparents</h2>
                <p>Choisissez le plan qui convient à votre équipe. Changez à tout moment.</p>
            </div>
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Starter</h3>
                        <div class="price">
                            <span class="currency">€</span>
                            <span class="amount">0</span>
                            <span class="period">/mois</span>
                        </div>
                        <p>Parfait pour débuter</p>
                    </div>
                    <ul class="pricing-features">
                        <li>1 workspace</li>
                        <li>Jusqu'à 1 000 lignes</li>
                        <li>Export CSV/Excel</li>
                        <li>Support par email</li>
                    </ul>
                    <button class="btn-secondary btn-full">Commencer Gratuitement</button>
                </div>
                <div class="pricing-card featured">
                    <div class="badge">Populaire</div>
                    <div class="pricing-header">
                        <h3>Pro</h3>
                        <div class="price">
                            <span class="currency">€</span>
                            <span class="amount">29</span>
                            <span class="period">/mois</span>
                        </div>
                        <p>Pour les équipes productives</p>
                    </div>
                    <ul class="pricing-features">
                        <li>Workspaces illimités</li>
                        <li>Jusqu'à 100 000 lignes</li>
                        <li>Tous les exports</li>
                        <li>Collaboration en équipe</li>
                        <li>Analytics avancés</li>
                        <li>Support prioritaire</li>
                    </ul>
                    <button class="btn-primary btn-full">Essayer 14 jours</button>
                </div>
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Enterprise</h3>
                        <div class="price">
                            <span class="amount">Sur mesure</span>
                        </div>
                        <p>Pour les grandes organisations</p>
                    </div>
                    <ul class="pricing-features">
                        <li>Données illimitées</li>
                        <li>SSO & sécurité avancée</li>
                        <li>API & intégrations</li>
                        <li>Support dédié 24/7</li>
                        <li>Formation équipe</li>
                        <li>SLA personnalisé</li>
                    </ul>
                    <button class="btn-secondary btn-full">Nous Contacter</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>Ce que disent nos utilisateurs</h2>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="quote">
                        <p>"DataTable Pro a révolutionné notre façon de traiter les données. Ce qui prenait des heures se fait maintenant en minutes."</p>
                    </div>
                    <div class="author">
                        <div class="avatar">S</div>
                        <div class="author-info">
                            <div class="name">Sophie Dubois</div>
                            <div class="role">Data Analyst - TechCorp</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote">
                        <p>"L'interface est si intuitive que même nos équipes non-techniques peuvent analyser leurs données facilement."</p>
                    </div>
                    <div class="author">
                        <div class="avatar">M</div>
                        <div class="author-info">
                            <div class="name">Marc Leroy</div>
                            <div class="role">Product Manager - StartupXYZ</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote">
                        <p>"Les fonctionnalités de collaboration ont transformé notre workflow. Toute l'équipe travaille sur les mêmes données en temps réel."</p>
                    </div>
                    <div class="author">
                        <div class="avatar">L</div>
                        <div class="author-info">
                            <div class="name">Laura Chen</div>
                            <div class="role">Head of Operations - Scale Inc</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Prêt à transformer vos données ?</h2>
                <p>Rejoignez des milliers d'équipes qui utilisent DataTable Pro pour analyser leurs données plus efficacement.</p>
                <div class="cta-actions">
                    <button class="btn-primary btn-large">Commencer Gratuitement</button>
                    <span class="cta-note">Aucune carte de crédit requise • Essai 14 jours</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <svg class="logo" viewBox="0 0 40 40" fill="none">
                            <rect width="40" height="40" rx="8" fill="url(#gradient2)"/>
                            <path d="M12 16h16M12 20h12M12 24h8" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <defs>
                                <linearGradient id="gradient2" x1="0" y1="0" x2="1" y2="1">
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
                        <a href="#features">Fonctionnalités</a>
                        <a href="#pricing">Tarifs</a>
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
                        <a href="#contact">Contact</a>
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
            <a href="#features">Fonctionnalités</a>
            <a href="#demo">Démo</a>
            <a href="#pricing">Tarifs</a>
            <a href="#contact">Contact</a>
            <button class="btn-secondary btn-full" onclick="window.location.href='{{ route('login') }}'">Connexion</button>
            <button class="btn-primary btn-full" onclick="window.location.href='{{ route('register') }}'">Essai Gratuit</button>
        </div>
    </div>

    <script src="{{ asset('index.js') }}"></script>
</body>
</html>
