<div class="auth-form">
    <!-- En-tête du formulaire -->
    <div class="auth-header">
        <h1>Créer un compte</h1>
        <p>Entrez vos informations ci-dessous pour créer votre compte</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="auth-form">
        <!-- Name -->
        <div class="auth-form-group">
            <label for="name">Nom complet</label>
            <input
                wire:model="name"
                id="name"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Nom complet"
            />
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <!-- Email Address -->
        <div class="auth-form-group">
            <label for="email">Adresse email</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                required
                autocomplete="email"
                placeholder="email@exemple.com"
            />
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div class="auth-form-group">
            <label for="password">Mot de passe</label>
            <input
                wire:model="password"
                id="password"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Mot de passe"
            />
            @error('password') <span class="error">{{ $message }}</span> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="auth-form-group">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input
                wire:model="password_confirmation"
                id="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirmer le mot de passe"
            />
            @error('password_confirmation') <span class="error">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="auth-form-submit">
            Créer le compte
        </button>
    </form>

    <div class="auth-form-links">
        Vous avez déjà un compte ?
        <a href="{{ route('login') }}" wire:navigate>Se connecter</a>
    </div>
</div>
