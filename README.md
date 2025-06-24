# DataTable Pro - Application Laravel + Livewire 3

Une application Laravel moderne avec Livewire 3 qui permet aux utilisateurs de créer plusieurs espaces de travail (workspaces), télécharger des fichiers CSV/Excel, les analyser et stocker chaque ligne sous forme de JSON dans une base de données. 

## 🚀 Fonctionnalités

### 📊 Gestion des Données
- **Table de données dynamique** avec recherche, tri et pagination
- **Recherche en temps réel** dans toutes les colonnes
- **Tri par colonnes** avec indicateurs visuels
- **Filtrage avancé** par colonnes spécifiques
- **Pagination** configurable

### 📁 Import/Export
- **Téléchargement de fichiers** CSV, XLSX, XLS (jusqu'à 10MB)
- **Traitement par batch** pour les gros fichiers
- **Export** des données filtrées en CSV ou Excel
- **Détection des doublons** avec hash des lignes
- **Gestion des erreurs** d'import détaillée

### 🏢 Workspaces Multi-tenants
- **Création de workspaces** illimitées par utilisateur
- **Bases de données SQLite isolées** par workspace
- **Gestion des permissions** (owner, admin, editor, viewer)
- **Invitation d'utilisateurs** aux workspaces
- **Basculement rapide** entre workspaces

### 📈 Surveillance et Statistiques
- **Dashboard avec graphiques** (à implémenter)
- **Historique des imports** avec détails d'erreurs
- **Statistiques** (taux de réussite, nombre de lignes, etc.)
- **Monitoring en temps réel**

### ✏️ Édition de Données
- **Modification de lignes** via modal
- **Suppression de lignes** individuelle ou en lot
- **Visualisation détaillée** des données en modal
- **Mises à jour en temps réel** avec Livewire

### 🎨 Interface Utilisateur Moderne
- **Design unifié** entre la landing page et les pages d'authentification
- **Navbar et footer cohérents** sur toutes les pages
- **Animations fluides** et effets visuels
- **Glass morphism design** avec backdrop-filter
- **Layout responsive** pour tous les appareils
- **Système d'authentification intégré** au design principal


## 🛠️ Installation

### Prérequis
- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite (ou MySQL/PostgreSQL)

### Installation rapide

```bash
# Cloner le projet
git clone <url-du-repo>
cd dataTable

# Démarrer l'application (script automatisé)
./start.sh
```

### Installation manuelle

```bash
# Installer les dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Lien de stockage
php artisan storage:link

# Démarrer les serveurs
php artisan serve
npm run dev
```

## 🧪 Tests

Le projet utilise **Pest** pour les tests :

```bash
# Exécuter tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=WorkspaceServiceTest
php artisan test --filter=ImportServiceTest
php artisan test --filter=DataTableComponentTest
```

### Couverture de tests
- ✅ Services (ImportService, WorkspaceService)
- ✅ Repositories (tous)
- ✅ Composants Livewire
- ✅ Upload et validation
- ✅ Recherche et pagination

## 🔧 Configuration

### Types de bases de données supportés
- **SQLite** (par défaut) - Recommandé pour les workspaces
- **MySQL** - Pour les deployments en production
- **PostgreSQL** - Support avancé

### Limites par défaut
- Taille de fichier : 10MB
- Batch processing : 1000 lignes
- Pagination : 15 éléments
- Workspaces : Illimités

### Stockage
- **Fichiers uploadés** : `storage/app/public/imports/`
- **Exports** : `storage/app/public/exports/`
- **Bases workspaces** : `database/workspaces/`
- **Assets statiques** : `public/index.css`, `public/index.js`
- **Images et favicons** : `public/` (favicon.ico, favicon.svg, etc.)

## 🎯 Utilisation

### 0. Page d'accueil et Authentification
1. Visiter la **landing page** avec design moderne
2. **S'inscrire** ou **se connecter** via l'interface intégrée
3. Interface d'authentification cohérente avec le design principal
4. Navigation fluide entre pages publiques et privées

### 1. Création d'un Workspace
1. Aller dans "My Workspaces"
2. Cliquer sur "Create Workspace"
3. Remplir le nom et la description
4. Choisir le type de base de données

### 2. Import de données
1. Sélectionner un workspace
2. Aller dans "Upload Files"
3. Sélectionner un fichier CSV/Excel
4. Attendre le traitement
5. Vérifier l'historique d'import

### 3. Gestion des données
1. Aller dans "Data Table"
2. Utiliser la recherche et les filtres
3. Trier par colonnes
4. Éditer/supprimer des lignes
5. Exporter les résultats

### 4. Collaboration
1. Aller dans les paramètres du workspace
2. Inviter des utilisateurs par email
3. Définir les rôles (viewer, editor, admin)
4. Gérer les permissions

### 5. Expérience Utilisateur
1. **Landing page** avec présentation du produit
2. **Design cohérent** entre toutes les pages
3. **Transitions fluides** et animations CSS
4. **Interface responsive** sur tous les appareils
5. **Feedback visuel** pour toutes les actions

## 🔒 Sécurité

- **Validation stricte** des fichiers uploadés
- **Isolation des workspaces** avec bases de données séparées
- **Middleware de vérification** workspace sélectionné
- **Gestion des permissions** par rôle
- **Hash des lignes** pour éviter les doublons
- **Sanitisation** des données JSON

## 📱 Interface Utilisateur

- **Design system unifié** avec navbar et footer cohérents
- **Pages d'authentification intégrées** au design principal
- **Responsive design** avec Tailwind CSS et CSS personnalisé
- **Animations CSS modernes** (fadeIn, slideIn, glass morphism)
- **Flux UI components** pour l'interface de l'application
- **Landing page professionnelle** avec sections marketing
- **Mises à jour en temps réel** avec Livewire
- **Indicateurs de progression** pour les uploads
- **Messages flash** pour le feedback utilisateur
- **Navigation par onglets** entre workspaces
- **Effets visuels avancés** (backdrop-filter, gradients, shadows)

## 🔮 Roadmap

### ✅ Version 1.0 (Actuelle)
- [x] Landing page moderne avec design system
- [x] Pages d'authentification intégrées au design
- [x] Workspaces multi-tenants avec isolation SQLite
- [x] Import/Export CSV et Excel avec gestion d'erreurs
- [x] Table de données interactive avec recherche et tri
- [x] Système de permissions et invitations
- [x] Architecture services/repositories complète
- [x] Tests Pest pour toutes les fonctionnalités

### Version 1.1
- [ ] Dashboard avec graphiques (Chart.js)
- [ ] Amélioration des animations et transitions
- [ ] Mode sombre/clair pour l'interface
- [ ] API REST pour intégrations externes
- [ ] Import par URL (Google Sheets, APIs)
- [ ] Notifications en temps réel
- [ ] Audit logs des modifications

### Version 1.2
- [ ] Templates d'import personnalisés
- [ ] Validation de données avancée
- [ ] Backup automatique des workspaces
- [ ] Intégration cloud (AWS S3, Google Drive)
- [ ] Mode hors ligne

### Version 2.0
- [ ] Machine Learning pour détection d'anomalies
- [ ] Visualisations de données intégrées
- [ ] Workflow automation
- [ ] Plugin système
- [ ] Mobile app

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit les changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.



---

**Développé avec ❤️ par Dark SHADOW 😎**

*Version 1.0 - Interface moderne et fonctionnalités complètes*
