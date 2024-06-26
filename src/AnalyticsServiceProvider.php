<?php

namespace AG\Analytics;

use AG\Analytics\Exceptions\InvalidConfiguration;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-analytics-legacy')
            ->hasConfigFile();
    }

    public function registeringPackage(): void
    {
        $this->app->bind(AnalyticsClient::class, function () {
            $analyticsConfig = config('analytics-legacy');

            return AnalyticsClientFactory::createForConfig($analyticsConfig);
        });

        $this->app->bind(Analytics::class, function () {
            $analyticsConfig = config('analytics-legacy');

            $this->guardAgainstInvalidConfiguration($analyticsConfig);

            $client = app(AnalyticsClient::class);

            return new Analytics($client, $analyticsConfig['view_id']);
        });

        $this->app->alias(Analytics::class, 'laravel-analytics-legacy');
    }

    protected function guardAgainstInvalidConfiguration(array $analyticsConfig = null): void
    {
        if (empty($analyticsConfig['view_id'])) {
            throw InvalidConfiguration::viewIdNotSpecified();
        }

        if (is_array($analyticsConfig['service_account_credentials_json'])) {
            return;
        }

        if (! file_exists($analyticsConfig['service_account_credentials_json'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($analyticsConfig['service_account_credentials_json']);
        }
    }
}
