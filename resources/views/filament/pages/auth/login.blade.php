<x-filament-panels::page.simple>
    <style>
        /* ── Background ── */
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%) !important;
        }
        .fi-body, .fi-simple-layout, .fi-simple-main {
            background: transparent !important;
            min-height: unset !important;
        }
        .login-wrapper {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
        }

        /* ── Sembunyikan header bawaan Filament ── */
        .fi-simple-header {
            display: none !important;
        }

        /* ── Card ── */
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(5, 150, 105, 0.12), 0 4px 16px rgba(0,0,0,0.06);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        /* ── Brand ── */
        .login-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        .login-logo {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.35);
        }
        .login-app-name {
            font-size: 1.75rem;
            font-weight: 700;
            color: #064e3b;
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .login-tagline {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
            line-height: 1.5;
        }

        /* ── Divider ── */
        .login-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.75rem 0;
        }
        .login-divider-line {
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        .login-divider-text {
            font-size: 0.75rem;
            color: #9ca3af;
            white-space: nowrap;
        }

        /* ── Google Button ── */
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .google-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
            color: #374151;
            text-decoration: none;
        }
        .google-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        /* ── Error Alert ── */
        .login-error {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 0.8125rem;
            color: #b91c1c;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }
        .login-error svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Footer ── */
        .login-footer {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.75rem;
            color: #9ca3af;
        }

        /* ── Decorative dots ── */
        .login-dots {
            position: absolute;
            opacity: 0.4;
            pointer-events: none;
        }

        /* Mobile tweaks */
        @media (max-width: 480px) {
            .login-card {
                border-radius: 20px;
                padding: 2rem 1.5rem;
                box-shadow: 0 8px 32px rgba(5, 150, 105, 0.1);
            }
            .fi-simple-main {
                padding: 1rem !important;
            }
        }
    </style>

    <div class="login-wrapper">
    <div class="login-card">

        {{-- Brand --}}
        <div class="login-brand">
            <div class="login-logo">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="white" style="width:34px;height:34px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                </svg>
            </div>
            <div>
                <div class="login-app-name">Waroong</div>
            </div>
            <p class="login-tagline">Kelola warung Anda lebih mudah,<br>cepat, dan terorganisir.</p>
        </div>

        {{-- Error --}}
        @if(session('oauth_error'))
            <div class="login-error">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                </svg>
                {{ session('oauth_error') }}
            </div>
        @endif

        {{-- Google OAuth Button --}}
        <a href="{{ route('auth.google') }}" class="google-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Masuk dengan Google
        </a>

        {{-- Footer --}}
        <div class="login-footer">
            &copy; {{ date('Y') }} Waroong &mdash; Aplikasi kasir untuk warung Indonesia
        </div>

    </div>
    </div>
</x-filament-panels::page.simple>
