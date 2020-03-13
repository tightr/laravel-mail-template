<?php


namespace Tightr\MailTemplate\Tests;

use Tightr\MailTemplate\Drivers\MailgunDriver;
use Tightr\MailTemplate\Drivers\MailjetDriver;
use Tightr\MailTemplate\Drivers\MandrillDriver;
use Tightr\MailTemplate\Drivers\NullDriver;
use Tightr\MailTemplate\Drivers\SendgridDriver;
use Tightr\MailTemplate\Drivers\SendinblueDriver;
use Tightr\MailTemplate\Exceptions\InvalidConfiguration;
use Tightr\MailTemplate\MailTemplate;

class MailTemplateFacadeTest extends TestCase
{
    /** @test */
    public function should_instantiate_facade_with_null_driver()
    {
        $mailTemplate = $this->app[MailTemplate::class];

        $this->assertInstanceOf(NullDriver::class, $mailTemplate);
    }

    /** @test */
    public function should_instantiate_facade_with_mailjet_driver()
    {
        config()->set('mail-template.driver', 'mailjet');
        config()->set('mail-template.mailjet.key', 'mailjet');
        config()->set('mail-template.mailjet.secret', 'mailjet');

        $mailTemplate = $this->app[MailTemplate::class];

        $this->assertInstanceOf(MailTemplate::class, $mailTemplate);
        $this->assertInstanceOf(MailjetDriver::class, $mailTemplate->driver);
    }

    /** @test */
    public function should_instantiate_facade_with_sendinblue_driver()
    {
        config()->set('mail-template.driver', 'sendinblue');
        config()->set('mail-template.sendinblue.key', 'sendinblue');

        $mailTemplate = $this->app[MailTemplate::class];

        $this->assertInstanceOf(MailTemplate::class, $mailTemplate);
        $this->assertInstanceOf(SendinblueDriver::class, $mailTemplate->driver);
    }

    /** @test */
    public function should_instantiate_facade_with_mandrill_driver()
    {
        config()->set('mail-template.driver', 'mandrill');
        config()->set('mail-template.mandrill.secret', 'mandrill');

        $mailTemplate = $this->app[MailTemplate::class];

        $this->assertInstanceOf(MailTemplate::class, $mailTemplate);
        $this->assertInstanceOf(MandrillDriver::class, $mailTemplate->driver);
    }

    /** @test */
    public function should_instantiate_facade_with_sendgrid_driver()
    {
        config()->set('mail-template.driver', 'sendgrid');
        config()->set('mail-template.sendgrid.key', 'sendgrid');

        $mailTemplate = $this->app[MailTemplate::class];

        $this->assertInstanceOf(MailTemplate::class, $mailTemplate);
        $this->assertInstanceOf(SendgridDriver::class, $mailTemplate->driver);
    }

    /** @test */
    public function should_instantiate_facade_with_mailgun_driver()
    {
        config()->set('mail-template.driver', 'mailgun');
        config()->set('mail-template.mailgun.key', 'mailgun-key');
        config()->set('mail-template.mailgun.domain', 'example.com');

        $mailTemplate = $this->app[MailTemplate::class];

        $this->assertInstanceOf(MailTemplate::class, $mailTemplate);
        $this->assertInstanceOf(MailgunDriver::class, $mailTemplate->driver);
    }

    /** @test */
    public function should_throw_error_on_register()
    {
        config()->set('mail-template.driver', 'invalid');

        $this->expectExceptionObject(InvalidConfiguration::driverNotFound('invalid'));
        $this->app[MailTemplate::class];
    }
}
