<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Widgets;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    private function cameraScannerHtml(): string
    {
        return <<<'HTML'
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div id="camera-scanner-modal"
     onclick="if(event.target===this) closeCameraScanner()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75);
            z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:white; border-radius:12px; padding:20px; width:340px;
                max-width:100%; max-height:90vh; overflow-y:auto;
                box-shadow:0 8px 32px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <strong style="font-size:15px;">📷 Scan Barcode</strong>
            <button onclick="closeCameraScanner()"
                    style="background:#f3f4f6; border:none; border-radius:50%; width:32px; height:32px;
                           font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
        </div>
        <div id="camera-reader" style="width:100%; border-radius:8px; overflow:hidden;"></div>
        <p style="font-size:11px; color:#888; text-align:center; margin-top:10px;">
            Arahkan kamera ke barcode produk
        </p>
        <button onclick="closeCameraScanner()"
                style="margin-top:12px; width:100%; padding:10px; background:#ef4444; color:white;
                       border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
            Tutup Kamera
        </button>
    </div>
</div>

<script>
let _html5QrCode = null;

function openCameraScanner(targetField = 'barcode_scan') {
    const modal = document.getElementById('camera-scanner-modal');
    modal.style.display = 'flex';

    _html5QrCode = new Html5Qrcode('camera-reader');
    _html5QrCode.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 260, height: 120 } },
        (decodedText) => {
            closeCameraScanner();
            const wire = _getLivewireComponent();
            if (wire) {
                wire.set('data.' + targetField, decodedText);
            }
        },
        () => {}
    ).catch(() => closeCameraScanner());
}

function closeCameraScanner() {
    const modal = document.getElementById('camera-scanner-modal');
    modal.style.display = 'none';
    if (_html5QrCode) {
        _html5QrCode.stop().then(() => {
            _html5QrCode.clear();
            _html5QrCode = null;
        }).catch(() => {});
    }
}

function _getLivewireComponent() {
    const el = document.querySelector('[wire\\:id]');
    if (!el) return null;
    return Livewire.find(el.getAttribute('wire:id'));
}
</script>
HTML;
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id("admin")
            ->path("admin")
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->colors([
                "primary" => Color::Emerald, // Warna hijau segar untuk tema keuangan/warung
            ])
            ->font("Poppins") // Menggunakan font modern agar lebih memanjakan mata
            ->brandName("Waroong") // Nama aplikasi di ujung kiri atas
            ->sidebarFullyCollapsibleOnDesktop() // UX: Sidebar bisa dilipat menyisakan ikon saja
            ->discoverResources(
                in: app_path("Filament/Resources"),
                for: "App\\Filament\\Resources",
            )
            ->discoverPages(
                in: app_path("Filament/Pages"),
                for: "App\\Filament\\Pages",
            )
            ->pages([\App\Filament\Pages\Dashboard::class])
            ->discoverWidgets(
                in: app_path("Filament/Widgets"),
                for: "App\\Filament\\Widgets",
            )
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->renderHook('panels::body.end', fn() => new HtmlString($this->cameraScannerHtml()))
            ->renderHook('panels::body.end', function () {
                $route = request()->route()?->getName() ?? '';
                if (str_contains($route, '.create') || str_contains($route, '.edit') || str_contains($route, 'auth.')) {
                    return '';
                }
                $url = route('filament.admin.resources.transactions.create');
                return new HtmlString(<<<HTML
                <style>
                    #transaction-fab { display: none; }
                    @media (max-width: 639px) { #transaction-fab { display: flex; } }
                    #transaction-fab:active { transform: scale(0.95) !important; }
                    @media (max-width: 639px) {
                        .fi-create-transaction-btn .fi-btn-label { display: none; }
                        .fi-create-transaction-btn { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                        .fi-header { flex-direction: row !important; align-items: center !important; justify-content: space-between !important; gap: 0.5rem !important; }
                        .fi-header > div:last-child { margin-top: 0 !important; }
                    }
                </style>
                <a id="transaction-fab" href="{$url}" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;align-items:center;gap:0.5rem;padding:0 1.25rem 0 1rem;height:3.25rem;border-radius:9999px;box-shadow:0 10px 25px rgba(0,0,0,0.35);background-color:#059669;color:white;text-decoration:none;transition:transform 0.1s;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:22px;height:22px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span style="font-size:0.9rem;font-weight:600;white-space:nowrap;">Transaksi Baru</span>
                </a>
                <script>
                    (function moveFab() {
                        var fab = document.getElementById('transaction-fab');
                        if (fab && fab.parentElement !== document.body) {
                            document.body.appendChild(fab);
                        }
                    })();
                    document.addEventListener('livewire:navigated', function () {
                        var fab = document.getElementById('transaction-fab');
                        if (fab && fab.parentElement !== document.body) {
                            document.body.appendChild(fab);
                        }
                    });
                </script>
                HTML);
            });
    }
}
