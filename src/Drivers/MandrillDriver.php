<?php

namespace Tightr\MailTemplate\Drivers;

use Tightr\MailTemplate\Exceptions\InvalidConfiguration;
use Tightr\MailTemplate\Exceptions\SendError;
use Mandrill;
use Mandrill_Error;

/**
 * Class MandrillDriver
 * @package Tightr\MailTemplate\Drivers
 */
class MandrillDriver implements Driver
{
    /** @var Mandrill|null  */
    public $client = null;

    /** @var array */
    public $body = [];

    /** @var array */
    public $message = [];

    /**
     * MandrillDriver constructor.
     * @param $config
     * @throws InvalidConfiguration
     * @throws Mandrill_Error
     */
    public function __construct($config)
    {
        if (!isset($config['secret'])) {
            throw InvalidConfiguration::invalidCredential('mandrill', 'secret');
        }

        $this->client = new Mandrill($config['secret']);
    }

    /**
     * @param string $name
     * @param string $email
     * @return Driver
     */
    public function setFrom(string $name, string $email): Driver
    {
        $this->message['from_name'] = $name;
        $this->message['from_email'] = $email;

        return $this;
    }

    /**
     * @param string $template
     * @return Driver
     */
    public function setTemplate($template): Driver
    {
        $this->body['template'] = $template;

        return $this;
    }

    /**
     * @param string $subjet
     * @return Driver
     */
    public function setSubject(string $subjet): Driver
    {
        $this->message['subject'] = $subjet;

        return $this;
    }

    /**
     * @param string $name
     * @param string $email
     * @return Driver
     */
    public function setRecipient(string $name, string $email): Driver
    {
        $this->message['to'][] = [
            'name' => $name,
            'email' => $email,
            'type' => 'to',
        ];

        return $this;
    }

    /**
     * @param array $variables
     * @return Driver
     */
    public function setVariables(array $variables): Driver
    {
        foreach ($variables as $variableKey => $variableValue) {
            $this->message['global_merge_vars'][] = [
                'name' => strtoupper($variableKey),
                'content' => $variableValue,
            ];
        }

        return $this;
    }

    /**
     * @param string $language
     * @return Driver
     */
    public function setLanguage(string $language): Driver
    {
        $this->message['global_merge_vars'][] = [
            'name' => 'MC_LANGUAGE',
            'content' => $language,
        ];

        return $this;
    }

    /**
     * @param string $file
     * @param string $name
     * @return Driver
     */
    public function addAttachment(string $file, string $name): Driver
    {
        $this->message['attachments'][] = [
            'type' => mime_content_type($file),
            'name' => $name,
            'content' => file_get_contents($file),
        ];

        return $this;
    }

    /**
     * @param bool $enable
     * @return Driver
     */
    public function trackClicks(bool $enable = false): Driver
    {
        $this->message['track_clicks'] = $enable;

        return $this;
    }

    /**
     * @param bool $enable
     * @return Driver
     */
    public function trackOpens(bool $enable = false): Driver
    {
        $this->message['track_opens'] = $enable;

        return $this;
    }

    /**
     * @return array
     * @throws SendError
     */
    public function send(): array
    {
        $response = [];
        try {
            $response = $this->client->messages->sendTemplate(
                $this->body['template'],
                [],
                $this->message
            );
        } catch (Mandrill_Error $exception) {
            throw SendError::responseError('mandrill', 0, $exception);
        }

        return $response;
    }

    public function toArray(): array
    {
        return [
            'body' => array_merge($this->body, [
                'message' => $this->message,
            ]),
        ];
    }
}
