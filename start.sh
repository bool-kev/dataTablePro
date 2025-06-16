#!/bin/bash

echo "🚀 Démarrage de l'application DataTable Laravel + Livewire"
echo "=================================================="

# Vérifier que les dépendances sont installées
if [ ! -d "vendor" ]; then
    echo "📦 Installation des dépendances PHP..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Installation des dépendances Node.js..."
    npm install
fi

# Créer le fichier .env s'il n'existe pas
if [ ! -f ".env" ]; then
    echo "⚙️ Création du fichier .env..."
    cp .env.example .env
    php artisan key:generate
fi

# Créer la base de données SQLite si elle n'existe pas
if [ ! -f "database/database.sqlite" ]; then
    echo "🗄️ Création de la base de données..."
    touch database/database.sqlite
fi

# Exécuter les migrations
echo "🔄 Exécution des migrations..."
php artisan migrate --force

# Exécuter les seeders
echo "🌱 Chargement des données de test..."
php artisan db:seed --force

# Créer le lien de stockage
echo "🔗 Création du lien de stockage..."
php artisan storage:link

# Démarrer le serveur de développement
echo "🌐 Démarrage du serveur de développement..."
echo "L'application sera disponible sur : http://localhost:8000"
echo "Utilisateur de test : test@example.com"
echo "Mot de passe par défaut : password"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter le serveur"

php artisan serve
