<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace Invitation - DataTable Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-table text-indigo-600 text-xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    DataTable Pro
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    You've been invited to collaborate!
                </p>
            </div>

            <!-- Invitation Card -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Join Workspace</h3>
                </div>

                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600">You're invited to join:</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $invitation->workspace->name }}</div>
                        @if($invitation->workspace->description)
                            <div class="text-sm text-gray-600 mt-1">{{ $invitation->workspace->description }}</div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">Role:</div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize">
                            {{ $invitation->role }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">Invited by:</div>
                        <div class="text-sm font-medium text-gray-900">{{ $invitation->inviter->name }}</div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-yellow-600 mr-2"></i>
                            <div class="text-sm text-yellow-800">
                                This invitation expires on {{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-3">
                    @auth
                        <form method="POST" action="{{ route('workspace.invitation.accept', $invitation->token) }}">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Accept Invitation
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}?invitation={{ $invitation->token }}" class="block w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors text-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Login to Accept
                        </a>
                        <a href="{{ route('register') }}?invitation={{ $invitation->token }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition-colors text-center">
                            <i class="fas fa-user-plus mr-2"></i>
                            Create Account & Accept
                        </a>
                    @endauth
                    
                    <form method="POST" action="{{ route('workspace.invitation.decline', $invitation->token) }}">
                        @csrf
                        <button type="submit" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Decline Invitation
                        </button>
                    </form>
                </div>

                <!-- Role Description -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm font-medium text-blue-900 mb-2">As a {{ $invitation->role }}, you'll be able to:</div>
                    <ul class="text-sm text-blue-800 space-y-1">
                        @if($invitation->role === 'viewer')
                            <li><i class="fas fa-eye mr-2"></i>View data and exports</li>
                            <li><i class="fas fa-chart-bar mr-2"></i>Access workspace dashboards</li>
                        @elseif($invitation->role === 'editor')
                            <li><i class="fas fa-eye mr-2"></i>View and edit data</li>
                            <li><i class="fas fa-upload mr-2"></i>Manage imports and exports</li>
                            <li><i class="fas fa-chart-bar mr-2"></i>Access workspace dashboards</li>
                        @elseif($invitation->role === 'admin')
                            <li><i class="fas fa-database mr-2"></i>Full access to workspace data</li>
                            <li><i class="fas fa-cogs mr-2"></i>Manage imports, exports, and settings</li>
                            <li><i class="fas fa-users mr-2"></i>Invite and manage other users</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} DataTable Pro. All rights reserved.</p>
            </div>
        </div>
    </div>

    @if (session('message'))
        <div class="fixed top-4 right-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded shadow-lg">
            {{ session('message') }}
        </div>
    @endif
</body>
</html>
