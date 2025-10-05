<?php

namespace App\Providers;

use App\Models\Service;
use App\Policies\ServicePolicy;
use App\Models\Demande;
use App\Policies\DemandePolicy;
use App\Models\Document;
use App\Policies\DocumentPolicy;
use App\Services\Otp\AxiomTextOtpSender;
use App\Services\Otp\OtpSender;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpSender::class, AxiomTextOtpSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Demande::class, DemandePolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
    }
}
