<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Workspace Collaboration</h2>
            <p class="text-gray-600">Manage members and invitations for {{ $workspace->name }}</p>
        </div>
        <button 
            wire:click="$set('showInviteForm', true)" 
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
        >
            <i class="fas fa-user-plus mr-2"></i>
            Invite User
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Invite User Form -->
    @if($showInviteForm)
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Invite New User</h3>
                <button 
                    wire:click="$set('showInviteForm', false)" 
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form wire:submit.prevent="inviteUser" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        wire:model="email" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="user@example.com"
                    >
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        Role
                    </label>
                    <select 
                        id="role"
                        wire:model="role" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        @foreach($this->roleOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex space-x-3">
                    <button 
                        type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        Send Invitation
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('showInviteForm', false)"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Pending Invitations -->
    @if($pendingInvitations->count() > 0)
        <div class="bg-white border border-gray-200 rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pending Invitations</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($pendingInvitations as $invitation)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i class="fas fa-envelope text-gray-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $invitation->email }}</div>
                                    <div class="text-sm text-gray-500">
                                        Invited by {{ $invitation->inviter->name }} • 
                                        <span class="capitalize bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">
                                            {{ $invitation->role }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Expires: {{ $invitation->expires_at->format('M j, Y g:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button 
                                wire:click="resendInvitation({{ $invitation->id }})"
                                class="text-indigo-600 hover:text-indigo-700 text-sm font-medium"
                            >
                                Resend
                            </button>
                            <button 
                                wire:click="cancelInvitation({{ $invitation->id }})"
                                class="text-red-600 hover:text-red-700 text-sm font-medium"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Current Members -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Members ({{ $members->total() }})</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($members as $member)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-medium text-sm">
                                    {{ substr($member->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                {{ $member->name }}
                                @if($member->id === $workspace->owner_id)
                                    <span class="ml-2 bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                        Owner
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">{{ $member->email }}</div>
                            <div class="text-xs text-gray-400">
                                Joined: {{ optional(\Carbon\Carbon::parse($member->pivot->joined_at))->format('M j, Y') ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($member->id !== $workspace->owner_id)
                            <select 
                                wire:change="updateUserRole({{ $member->id }}, $event.target.value)"
                                class="text-sm border border-gray-300 rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                @foreach($this->roleOptions as $value => $label)
                                    <option 
                                        value="{{ $value }}" 
                                        {{ $member->pivot->role === $value ? 'selected' : '' }}
                                    >
                                        {{ ucfirst($value) }}
                                    </option>
                                @endforeach
                            </select>
                            <button 
                                wire:click="confirmRemoveUser({{ $member->id }})"
                                class="text-red-600 hover:text-red-700 text-sm"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        @else
                            <span class="text-sm text-gray-500 px-2 py-1">Owner</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No members found.
                </div>
            @endforelse
        </div>
        
        @if($members->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $members->links() }}
            </div>
        @endif
    </div>

    <!-- Remove User Confirmation Modal -->
    @if($showConfirmRemove)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-2">Remove User</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to remove this user from the workspace? 
                            They will lose access immediately and will need to be re-invited to regain access.
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button 
                            wire:click="removeUser({{ $userToRemove }})"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 mr-2"
                        >
                            Remove
                        </button>
                        <button 
                            wire:click="cancelRemove"
                            class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
