<div class="auth-form">
    <!-- En-tête du formulaire -->
    <div class="auth-header">
        <h1>Connectez-vous à votre compte</h1>
        <p>Entrez vos identifiants ci-dessous pour vous connecter</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="auth-form">
        <!-- Email Address -->
        <div class="auth-form-group">
            <label for="email">Adresse email</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@exemple.com"
            />
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div class="auth-form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label for="password">Mot de passe</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color: #2684FF; font-size: 0.9rem; text-decoration: none;" wire:navigate>
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>
            <input
                wire:model="password"
                id="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Mot de passe"
            />
            @error('password') <span class="error">{{ $message }}</span> @enderror
        </div>

        <!-- Remember Me -->
        <div class="auth-form-remember">
            <input 
                wire:model="remember" 
                id="remember" 
                type="checkbox"
                style="width: 16px; height: 16px; accent-color: #2684FF;"
            />
            <label for="remember" style="margin: 0; font-weight: normal;">Se souvenir de moi</label>
        </div>

        <button type="submit" class="auth-form-submit">
            Se connecter
        </button>
    </form>

    @if (Route::has('register'))
        <div class="auth-form-links">
            Vous n'avez pas de compte ?
            <a href="{{ route('register') }}" wire:navigate>Créer un compte</a>
        </div>
    @endif
</div>
