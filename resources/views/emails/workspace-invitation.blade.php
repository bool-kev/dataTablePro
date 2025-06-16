<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace Invitation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #374151;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .content {
            padding: 2rem;
        }
        .workspace-info {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        .workspace-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .role-badge {
            display: inline-block;
            background-color: #ddd6fe;
            color: #5b21b6;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .button-container {
            text-align: center;
            margin: 2rem 0;
        }
        .button {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 0 0.5rem;
            transition: all 0.2s;
        }
        .button-accept {
            background-color: #10b981;
            color: white;
        }
        .button-accept:hover {
            background-color: #059669;
        }
        .button-decline {
            background-color: #ef4444;
            color: white;
        }
        .button-decline:hover {
            background-color: #dc2626;
        }
        .footer {
            background-color: #f9fafb;
            padding: 1.5rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            border-top: 1px solid #e5e7eb;
        }
        .expiry-notice {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 1rem;
            margin: 1.5rem 0;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗂️ DataTable Pro</h1>
            <p>You've been invited to collaborate!</p>
        </div>
        
        <div class="content">
            <p>Hello!</p>
            
            <p><strong>{{ $inviter->name }}</strong> has invited you to join the workspace <strong>{{ $workspace->name }}</strong> on DataTable Pro.</p>
            
            <div class="workspace-info">
                <div class="workspace-name">{{ $workspace->name }}</div>
                @if($workspace->description)
                    <p style="margin: 0.5rem 0 1rem 0; color: #6b7280;">{{ $workspace->description }}</p>
                @endif
                <div>
                    <span class="role-badge">{{ $invitation->role }}</span>
                </div>
            </div>
            
            <p>As a <strong>{{ $invitation->role }}</strong>, you will be able to:</p>
            <ul style="color: #6b7280;">
                @if($invitation->role === 'viewer')
                    <li>View data and exports</li>
                    <li>Access workspace dashboards</li>
                @elseif($invitation->role === 'editor')
                    <li>View and edit data</li>
                    <li>Manage imports and exports</li>
                    <li>Access workspace dashboards</li>
                @elseif($invitation->role === 'admin')
                    <li>Full access to workspace data</li>
                    <li>Manage imports, exports, and settings</li>
                    <li>Invite and manage other users</li>
                @endif
            </ul>
            
            <div class="button-container">
                <a href="{{ $acceptUrl }}" class="button button-accept">Accept Invitation</a>
                <a href="{{ $declineUrl }}" class="button button-decline">Decline</a>
            </div>
            
            <div class="expiry-notice">
                ⏰ <strong>Note:</strong> This invitation will expire on {{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}.
            </div>
            
            <p style="color: #6b7280; font-size: 0.875rem;">
                If you don't have a DataTable Pro account yet, you'll be able to create one when you accept this invitation.
            </p>
        </div>
        
        <div class="footer">
            <p>This invitation was sent by {{ $inviter->name }} ({{ $inviter->email }})</p>
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
            <p>&copy; {{ date('Y') }} DataTable Pro. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
