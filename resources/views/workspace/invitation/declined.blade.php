<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Declined - DataTable Pro</title>
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

            <!-- Declined Card -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times text-gray-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Invitation Declined</h3>
                </div>

                <div class="text-center space-y-4">
                    <p class="text-gray-600">
                        You have declined the invitation to join <strong>{{ $workspaceName }}</strong>.
                    </p>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            If you change your mind, you can ask the workspace owner to send you a new invitation.
                        </p>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="mt-6">
                    <a href="{{ route('login') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition-colors text-center">
                        <i class="fas fa-home mr-2"></i>
                        Go to DataTable Pro
                    </a>
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
