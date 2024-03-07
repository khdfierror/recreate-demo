<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use App\Filament\Pages\Profile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\View\Components\Modal;
use Filament\Tables\Table;
use Filament\Widgets;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Modal::closedByClickingAway(false);
        Table::configureUsing(function (Table $table): void {
            $table->defaultPaginationPageOption(25)
                ->paginationPageOptions([10, 25, 50, 100])
                ->searchDebounce('1000ms');
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->url(fn () => Profile::getUrl()),
            ])
            ->navigationItems([
                NavigationItem::make('pulse')
                    ->label('Pulse')
                    ->icon('carbon-activity')
                    ->sort(999)
                    ->group('Pengaturan')
                    ->url(fn () => url(config('pulse.path')), shouldOpenInNewTab: true),
            ])
            ->path('/')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->spa()
            ->font('DM Sans')
            ->maxContentWidth('full')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook('panels::styles.before', fn (): string => Blade::render(<<<'HTML'
                <style>
                    /** Setting Base Font */
                    html, body{
                        font-size: 14px;
                    }
                </style>
            HTML))
            ->renderHook('panels::resource.pages.list-records.table.before', fn (): View => view('components.total-records'))
            ->renderHook('panels::resource.pages.list-records.table.after', fn (): string => Blade::render(<<<'HTML'
                <x-modal-loading wire:loading wire:target="gotoPage,nextPage,previousPage,sortTable,tableRecordsPerPage,tableGrouping,tableGroupingDirection,tableSearch,tableFilters,toggledTableColumns,resetTableSearch,mountTableAction" />
            HTML))
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                Profile::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
