<?php

namespace Tightr\MailTemplate\Tests\Mailgun;

use Tightr\MailTemplate\Drivers\MailgunDriver;
use Tightr\MailTemplate\Exceptions\InvalidConfiguration;
use Tightr\MailTemplate\Exceptions\SendError;
use Tightr\MailTemplate\MailTemplate;
use Tightr\MailTemplate\Tests\TestCase;
use Mailgun\Api\Message;
use Mailgun\Exception\HttpClientException;
use Mailgun\Mailgun;
use Mailgun\Model\Message\SendResponse;
use Mockery;

class MailTemplateTest extends TestCase
{
    /** @var MailgunDriver */
    protected $driver;

    /** @var Mockery\Mock */
    protected $client;

    /** @var MailTemplate */
    protected $mailTemplate;


    public function setUp(): void
    {
        $this->client = Mockery::mock(Mailgun::class);

        $this->driver = new MailgunDriver([
            'key' => 'test-key',
            'domain' => 'test.mailgun.org'
        ]);
        $this->driver->client = $this->client;

        $this->mailTemplate = new MailTemplate($this->driver);
    }


    public function tearDown(): void
    {
        parent::tearDown();
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        Mockery::close();
    }

    /** @test */
    public function should_throw_error_with_key()
    {
        $this->expectExceptionObject(InvalidConfiguration::invalidCredential('mailgun', 'key'));

        $driver = new MailgunDriver([
            'domain' => 'test',
        ]);
    }

    /** @test */
    public function should_throw_error_with_domain()
    {
        $this->expectExceptionObject(InvalidConfiguration::invalidCredential('mailgun', 'domain'));

        $driver = new MailgunDriver([
            'key' => 'test',
        ]);
    }


    /** @test */
    public function should_set_from()
    {
        $this->mailTemplate->setFrom('test_from_name', 'test_from_email');
        $this->assertTrue($this->driver->message['from'] === "test_from_name <test_from_email>");
    }

    /** @test */
    public function should_set_recipient()
    {
        $this->mailTemplate->setRecipient('test_recipient_name', 'test_recipient_email');
        $this->assertTrue($this->driver->message['to'] === "test_recipient_name <test_recipient_email>");
    }

    /** @test */
    public function should_set_template()
    {
        $this->mailTemplate->setTemplate('test');
        $this->assertTrue($this->driver->message['template'] === 'test');
    }

    /** @test */
    public function should_set_variables()
    {
        $this->mailTemplate->setVariables([
            'test_key' => 'test_value'
        ]);
        $this->assertJsonStringEqualsJsonString('{"test_key":"test_value"}', $this->driver->message['h:X-Mailgun-Variables']);
    }

    /** @test */
    public function should_return_array_from_to_array()
    {
        $this->mailTemplate->setSubject('test_subject');
        $this->mailTemplate->setFrom('test_from_name', 'test_from_email');
        $this->mailTemplate->setRecipient('test_recipient_name', 'test_recipient_email');
        $this->mailTemplate->setLanguage('test');
        $this->mailTemplate->setTemplate(12);
        $this->mailTemplate->setVariables([
            'test_key' => 'test_value'
        ]);

        $body = $this->mailTemplate->toArray();

        $this->assertTrue(isset($body['body']));
        $this->assertTrue(isset($body['body']['Messages']));
        $this->assertCount(1, $body['body']['Messages']);
    }


    /** @test */
    public function should_receive_send_successfully()
    {
        $this->mailTemplate->setSubject('test_subject');
        $this->mailTemplate->setFrom('martin', 'martin@dansmaculotte.fr');
        $this->mailTemplate->setRecipient('gael', 'martin@dansmaculotte.fr');
        $this->mailTemplate->setLanguage('test');
        $this->mailTemplate->setTemplate('test');
        $this->mailTemplate->setVariables([
            'test_key' => 'test_value'
        ]);

        $messages = Mockery::mock(Message::class);
        $this->client->shouldReceive('messages')->andReturn($messages);

        $response = Mockery::mock(SendResponse::class);
        $messages->shouldReceive('send')->andReturn($response);

        $response->shouldReceive('getId')->andReturn('testId');
        $response->shouldReceive('getMessage')->andReturn('testMessage');

        $return = $this->mailTemplate->send();

        $this->assertEquals('testId', $return['id']);
        $this->assertEquals('testMessage', $return['message']);
    }

    /** @test */
    public function should_receive_send_and_throw_error()
    {
        $this->mailTemplate->setSubject('test_subject');
        $this->mailTemplate->setFrom('martin', 'martin@dansmaculotte.fr');
        $this->mailTemplate->setRecipient('gael', 'martin@dansmaculotte.fr');
        $this->mailTemplate->setLanguage('test');
        $this->mailTemplate->setTemplate('test');
        $this->mailTemplate->setVariables([
            'test_key' => 'test_value'
        ]);

        $messages = Mockery::mock(Message::class);
        $this->client->shouldReceive('messages')->andReturn($messages);

        $messages->shouldReceive('send')->andThrow(HttpClientException::class);

        $this->expectExceptionObject(SendError::responseError('mailgun'));

        $this->mailTemplate->send();
    }
}
