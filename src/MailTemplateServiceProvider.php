<?php

namespace Tightr\MailTemplate;

use Tightr\MailTemplate\Drivers\MailgunDriver;
use Tightr\MailTemplate\Drivers\MailjetDriver;
use Tightr\MailTemplate\Drivers\MandrillDriver;
use Tightr\MailTemplate\Drivers\NullDriver;
use Tightr\MailTemplate\Drivers\SendgridDriver;
use Tightr\MailTemplate\Drivers\SendinblueDriver;
use Tightr\MailTemplate\Exceptions\InvalidConfiguration;
use Illuminate\Support\ServiceProvider;

class MailTemplateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mail-template.php', 'mail-template');

        $this->publishes([
            __DIR__.'/../config/mail-template.php' => config_path('mail-template.php'),
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(MailTemplate::class, function () {
            $driver = config('mail-template.driver', null);
            if (is_null($driver) || $driver === 'log') {
                return new NullDriver($driver === 'log');
            }

            switch ($driver) {
                case 'mailjet':
                    $driver = new MailjetDriver(config('mail-template.mailjet'));
                    break;
                case 'mandrill':
                    $driver = new MandrillDriver(config('mail-template.mandrill'));
                    break;
                case 'sendgrid':
                    $driver = new SendgridDriver(config('mail-template.sendgrid'));
                    break;
                case 'mailgun':
                    $driver = new MailgunDriver(config('mail-template.mailgun'));
                    break;
                case 'sendinblue':
                    $driver = new SendinblueDriver(config('mail-template.sendinblue'));
                    break;
                default:
                    throw InvalidConfiguration::driverNotFound($driver);
                    break;
            }

            return new MailTemplate($driver);
        });

        $this->app->alias(MailTemplate::class, 'mail-template');
    }
}
