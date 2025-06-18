#!/bin/bash

# Script pour exécuter les tests avec Pest

echo "🧪 Préparation de l'environnement de test..."

# Copier la configuration de test
if [ -f "phpunit.xml.new" ]; then
    cp phpunit.xml.new phpunit.xml
    echo "✅ Configuration PHPUnit mise à jour"
fi

# Vérifier que Pest est installé
if ! composer show pestphp/pest &> /dev/null; then
    echo "⚠️  Pest n'est pas installé. Installation en cours..."
    composer require --dev pestphp/pest pestphp/pest-plugin-laravel
fi

# Créer la base de données de test si nécessaire
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
    echo "✅ Base de données SQLite créée"
fi

# Exécuter les migrations pour les tests
echo "🔄 Exécution des migrations de test..."
php artisan migrate:fresh --env=testing --seed

echo "🚀 Exécution des tests..."

# Exécuter tous les tests
./vendor/bin/pest --colors=always

# Exécuter des groupes de tests spécifiques
echo ""
echo "📊 Exécution des tests par catégorie..."

echo "🔵 Tests unitaires..."
./vendor/bin/pest tests/Unit --colors=always

echo "🟢 Tests de fonctionnalités..."
./vendor/bin/pest tests/Feature --colors=always

echo "🟡 Tests d'intégration..."
./vendor/bin/pest tests/Feature/IntegrationTest.php --colors=always

# Générer un rapport de couverture si possible
if command -v xdebug &> /dev/null; then
    echo "📈 Génération du rapport de couverture..."
    ./vendor/bin/pest --coverage-html coverage
    echo "✅ Rapport de couverture généré dans le dossier 'coverage'"
fi

echo ""
echo "✅ Tests terminés !"
echo "📝 Pour exécuter des tests spécifiques :"
echo "   ./vendor/bin/pest tests/Feature/ImportServiceTest.php"
echo "   ./vendor/bin/pest --filter 'can process a CSV file'"
echo "   ./vendor/bin/pest tests/Unit"
