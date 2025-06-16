# 🤝 Système de Collaboration DataTable Pro

## 📋 Vue d'ensemble

Le système de collaboration permet aux propriétaires de workspace d'inviter des utilisateurs avec différents niveaux de permissions (viewer, editor, admin) pour collaborer sur leurs données.

## 🗂️ Fichiers créés/modifiés

### Modèles et Migrations
- `app/Models/WorkspaceInvitation.php` - Modèle pour les invitations
- `database/migrations/2025_06_16_190829_create_workspace_invitations_table.php` - Table des invitations
- `app/Models/Workspace.php` - Relations ajoutées pour les invitations

### Services et Contrôleurs
- `app/Services/WorkspaceInvitationService.php` - Logique métier des invitations
- `app/Http/Controllers/WorkspaceInvitationController.php` - Gestion des URLs d'invitation
- `app/Mail/WorkspaceInvitationMail.php` - Email d'invitation

### Composants Livewire
- `app/Livewire/WorkspaceCollaboration.php` - Interface de gestion des collaborateurs
- `resources/views/livewire/workspace-collaboration.blade.php` - Vue de gestion

### Templates et Vues
- `resources/views/emails/workspace-invitation.blade.php` - Template email d'invitation
- `resources/views/workspace/invitation/show.blade.php` - Page d'acceptation d'invitation
- `resources/views/workspace/invitation/invalid.blade.php` - Page d'invitation invalide
- `resources/views/workspace/invitation/declined.blade.php` - Page d'invitation refusée
- `resources/views/collaboration.blade.php` - Page de collaboration

### Routes et Navigation
- `routes/web.php` - Routes d'invitation ajoutées
- `resources/views/components/layouts/app/sidebar.blade.php` - Lien de navigation ajouté

### Outils de test
- `database/seeders/TestCollaborationSeeder.php` - Données de test
- `app/Console/Commands/GenerateInvitationLink.php` - Génération de liens de test

## 🔐 Niveaux de permissions

### Viewer
- Peut consulter les données et les exports
- Accès aux tableaux de bord du workspace

### Editor  
- Toutes les permissions du Viewer
- Peut modifier et éditer les données
- Peut gérer les imports et exports

### Admin
- Toutes les permissions de l'Editor
- Peut inviter et gérer d'autres utilisateurs
- Peut modifier les paramètres du workspace

### Owner
- Toutes les permissions avec contrôle total
- Ne peut pas être supprimé ou voir son rôle modifié

## 🚀 Fonctionnalités implémentées

### 1. Système d'invitation par email
- Envoi d'emails d'invitation avec liens sécurisés
- Token unique avec expiration (7 jours par défaut)
- Templates HTML responsifs pour les emails

### 2. Interface de gestion des collaborateurs
- Liste des membres actuels avec leurs rôles
- Invitations en attente avec possibilité de renvoyer/annuler
- Modification des rôles en temps réel
- Suppression de membres (sauf le propriétaire)

### 3. Pages d'acceptation d'invitation
- Page d'invitation avec détails du workspace et du rôle
- Gestion des utilisateurs connectés et non-connectés
- Redirection vers login/register si nécessaire
- Messages d'erreur pour les invitations invalides/expirées

### 4. Système de permissions
- Vérification des permissions dans `Workspace::canUserAccess()`
- Contrôles d'accès dans tous les composants
- Protection des routes sensibles

## 🧪 Tests et validation

### Données de test créées
```bash
php artisan db:seed --class=TestCollaborationSeeder
```

Utilisateurs créés :
- **owner@datatable.com** (password: password) - Propriétaire
- **collaborator@datatable.com** (password: password) - Collaborateur existant

### Génération de liens d'invitation
```bash
php artisan invitation:link [email]
```

## 🔄 Flux d'utilisation

1. **Inviter un utilisateur** :
   - Aller dans Collaboration > Invite User
   - Saisir email et sélectionner le rôle
   - L'utilisateur reçoit un email avec le lien

2. **Accepter une invitation** :
   - Cliquer sur le lien dans l'email
   - Se connecter ou créer un compte si nécessaire
   - Être automatiquement ajouté au workspace

3. **Gérer les collaborateurs** :
   - Voir tous les membres actuels
   - Modifier les rôles
   - Supprimer des membres
   - Gérer les invitations en attente

## 📧 Configuration email

Pour le développement, configurez les variables d'environnement email dans `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@datatable.pro"
MAIL_FROM_NAME="DataTable Pro"
```

## 🔧 Points d'amélioration futurs

1. **Notifications en temps réel** quand quelqu'un accepte/refuse une invitation
2. **Limitation du nombre d'invitations** par workspace ou par utilisateur
3. **Templates d'invitation personnalisables** par workspace
4. **Audit trail** des actions de collaboration
5. **Invitations en lot** avec import CSV
6. **Permissions granulaires** pour des fonctionnalités spécifiques

## 🐛 Dépannage

### Erreurs communes
- **"Address already in use"** : Utiliser un port différent (`php artisan serve --port=8001`)
- **Erreur d'email** : Vérifier la configuration MAIL dans `.env`
- **Token d'invitation invalide** : Vérifier que l'invitation n'est pas expirée

### Logs utiles
```bash
tail -f storage/logs/laravel.log
```

Le système de collaboration est maintenant entièrement fonctionnel et prêt pour la production ! 🎉
