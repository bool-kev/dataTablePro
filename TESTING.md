# Tests Documentation

Ce projet utilise **Pest** comme framework de test, une surcouche moderne et expressive de PHPUnit spécialement conçue pour Laravel.

## 📁 Structure des Tests

```
tests/
├── Pest.php                    # Configuration globale Pest et helpers
├── TestCase.php                # Classe de base pour les tests
├── Feature/                    # Tests de fonctionnalités (tests d'intégration)
│   ├── ImportServiceTest.php           # Tests du service d'import
│   ├── DataTableComponentTest.php      # Tests du composant DataTable Livewire
│   ├── WorkspaceDataIsolationTest.php  # Tests d'isolation des workspaces
│   ├── Components/
│   │   └── FileUploadTest.php          # Tests du composant FileUpload
│   ├── Services/
│   │   └── ExportServiceTest.php       # Tests du service d'export
│   ├── Api/
│   │   └── DataManagementApiTest.php   # Tests des API REST
│   └── IntegrationTest.php             # Tests d'intégration complets
└── Unit/                       # Tests unitaires
    ├── Models/
    │   ├── ImportedDataTest.php        # Tests du modèle ImportedData
    │   └── ImportHistoryTest.php       # Tests du modèle ImportHistory
    └── Repositories/
        └── ImportedDataRepositoryTest.php # Tests du repository
```

## 🚀 Exécution des Tests

### Exécution rapide
```bash
# Linux/Mac
./run-tests.sh

# Windows
run-tests.bat
```

### Commandes manuelles
```bash
# Tous les tests
./vendor/bin/pest

# Tests par dossier
./vendor/bin/pest tests/Unit
./vendor/bin/pest tests/Feature

# Test spécifique
./vendor/bin/pest tests/Feature/ImportServiceTest.php

# Filtrer par nom
./vendor/bin/pest --filter "can process a CSV file"

# Avec couverture de code
./vendor/bin/pest --coverage
```

## 📋 Types de Tests

### 1. Tests Unitaires (`tests/Unit/`)
Tests isolés des classes individuelles sans dépendances externes.

**Exemples :**
- Tests des modèles Eloquent
- Tests des repositories
- Tests des services métier
- Validation des transformations de données

### 2. Tests de Fonctionnalités (`tests/Feature/`)
Tests des fonctionnalités complètes incluant les interactions base de données.

**Exemples :**
- Import de fichiers CSV/Excel
- Composants Livewire
- API endpoints
- Workflows complets

### 3. Tests d'Intégration (`tests/Feature/IntegrationTest.php`)
Tests de bout en bout validant les workflows complets.

**Exemples :**
- Upload → Import → Affichage → Export
- Interactions entre composants
- Performance avec grandes données

## 🛠️ Helpers et Utilitaires

Le fichier `tests/Pest.php` contient des helpers réutilisables :

```php
// Créer un utilisateur avec workspace
['user' => $user, 'workspace' => $workspace] = createUserWithWorkspace();

// Créer un historique d'import
$importHistory = createImportHistory($workspace);

// Créer des données importées
$data = createImportedData($importHistory, ['name' => 'John']);

// Créer des fichiers de test
$csvFile = createCsvFile('test.csv');
$excelFile = createExcelFile('test.xlsx');

// Authentifier dans un workspace
actingAsUserInWorkspace($user, $workspace);
```

## 🎯 Expectations Personnalisées

```php
// Valider du JSON
expect($jsonString)->toBeValidJson();

// Vérifier l'accès workspace
expect($user)->toHaveWorkspaceAccess($workspace);

// Vérifier l'appartenance à un workspace
expect($data)->toBeInWorkspace($workspace);
```

## 📊 Couverture de Tests

### Objectifs de Couverture
- **Modèles** : 95%+ (logique métier critique)
- **Services** : 90%+ (orchestration des opérations)
- **Repositories** : 85%+ (accès aux données)
- **Composants Livewire** : 80%+ (interactions utilisateur)
- **API** : 85%+ (interfaces externes)

### Générer le Rapport
```bash
./vendor/bin/pest --coverage-html coverage
```

## 🧪 Bonnes Pratiques

### 1. Organisation des Tests
- Un fichier de test par classe testée
- Groupement par fonctionnalités avec `describe()`
- Tests atomiques et indépendants

### 2. Naming Convention
```php
it('can process a CSV file successfully')
it('validates required fields')
it('handles duplicate data correctly')
```

### 3. Structure d'un Test
```php
describe('Feature Name', function () {
    beforeEach(function () {
        // Setup commun
    });

    it('describes what the test does', function () {
        // Arrange
        $data = setupTestData();
        
        // Act
        $result = performAction($data);
        
        // Assert
        expect($result)->toBe($expected);
    });
});
```

### 4. Isolation des Données
- Utilisation de `RefreshDatabase` trait
- Factory pour créer les données de test
- Isolation par workspace

### 5. Tests de Performance
```php
it('processes large files efficiently', function () {
    $startTime = microtime(true);
    
    // Opération à tester
    processLargeFile($file);
    
    $endTime = microtime(true);
    expect($endTime - $startTime)->toBeLessThan(5.0);
});
```

## 🔧 Configuration

### Variables d'Environnement
```env
# Base de données de test
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Cache et sessions
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync

# Stockage de test
FILESYSTEM_DISK=testing
```

### Mock et Stubs
```php
// Mock d'un service
$this->mock(ImportService::class)
    ->shouldReceive('processFile')
    ->once()
    ->andReturn($expectedResult);

// Fake du stockage
Storage::fake('public');

// Fake d'Excel
Excel::fake();
```

## 📈 Métriques et Monitoring

### Tests de Performance
- Temps d'import par taille de fichier
- Temps de recherche par nombre d'enregistrements
- Mémoire utilisée pour les grandes opérations

### Tests de Charge
- Import de fichiers > 1MB
- Affichage de > 10k enregistrements
- Recherches sur > 100k lignes

## 🚨 CI/CD Integration

### GitHub Actions
```yaml
- name: Run Tests
  run: |
    php artisan migrate:fresh --env=testing
    ./vendor/bin/pest --coverage --min=80
```

### Hooks Git
```bash
# pre-commit hook
./vendor/bin/pest --bail
```

## 📝 Debugging

### Dump et Debug
```php
// Dans un test
dump($variable);
ray($data); // Si Ray est installé

// Arrêter sur échec
./vendor/bin/pest --stop-on-failure
```

### Mode Verbose
```bash
./vendor/bin/pest -v
./vendor/bin/pest --debug
```

## 🔍 Tests Spécifiques par Fonctionnalité

### Import de Données
- Validation des formats de fichiers
- Gestion des erreurs d'encodage
- Détection des doublons
- Performance sur gros volumes

### Composants Livewire
- Rendu des composants
- Interactions utilisateur
- Mises à jour en temps réel
- Validation côté client

### API REST
- Authentification et autorisation
- Validation des paramètres
- Codes de retour HTTP
- Rate limiting

### Isolation des Workspaces
- Accès aux données
- Permissions utilisateur
- Séparation des imports
- Export sélectif

---

**💡 Tip** : Utilisez `./vendor/bin/pest --parallel` pour exécuter les tests en parallèle et accélérer l'exécution.
