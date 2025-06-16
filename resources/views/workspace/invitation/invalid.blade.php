<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Invitation - DataTable Pro</title>
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
            </div>

            <!-- Error Card -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        @if(isset($status) && $status === 'expired')
                            <i class="fas fa-clock text-red-600 text-2xl"></i>
                        @else
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        @if(isset($status) && $status === 'expired')
                            Invitation Expired
                        @else
                            Invalid Invitation
                        @endif
                    </h3>
                </div>

                <div class="text-center space-y-4">
                    <p class="text-gray-600">
                        {{ $error }}
                    </p>

                    @if(isset($status) && $status === 'expired')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                This invitation has expired. Please contact the workspace owner to request a new invitation.
                            </p>
                        </div>
                    @elseif(isset($status) && $status === 'accepted')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                This invitation has already been accepted. You should already have access to the workspace.
                            </p>
                        </div>
                    @elseif(isset($status) && $status === 'declined')
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-times-circle mr-2"></i>
                                This invitation was declined.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition-colors text-center">
                            <i class="fas fa-home mr-2"></i>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition-colors text-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Login to DataTable Pro
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} DataTable Pro. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
