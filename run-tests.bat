@echo off
echo 🧪 Préparation de l'environnement de test...

REM Copier la configuration de test
if exist "phpunit.xml.new" (
    copy phpunit.xml.new phpunit.xml >nul
    echo ✅ Configuration PHPUnit mise à jour
)

REM Créer la base de données de test si nécessaire
if not exist "database\database.sqlite" (
    type nul > database\database.sqlite
    echo ✅ Base de données SQLite créée
)

REM Exécuter les migrations pour les tests
echo 🔄 Exécution des migrations de test...
php artisan migrate:fresh --env=testing --seed

echo 🚀 Exécution des tests...

REM Exécuter tous les tests
vendor\bin\pest --colors=always

echo.
echo 📊 Exécution des tests par catégorie...

echo 🔵 Tests unitaires...
vendor\bin\pest tests\Unit --colors=always

echo 🟢 Tests de fonctionnalités...
vendor\bin\pest tests\Feature --colors=always

echo 🟡 Tests d'intégration...
vendor\bin\pest tests\Feature\IntegrationTest.php --colors=always

echo.
echo ✅ Tests terminés !
echo 📝 Pour exécuter des tests spécifiques :
echo    vendor\bin\pest tests\Feature\ImportServiceTest.php
echo    vendor\bin\pest --filter "can process a CSV file"
echo    vendor\bin\pest tests\Unit

pause
