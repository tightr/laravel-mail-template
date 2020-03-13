<?php

namespace Tightr\MailTemplate\Drivers;

use Tightr\MailTemplate\Exceptions\InvalidConfiguration;
use Tightr\MailTemplate\Exceptions\SendError;
use Mailgun\Exception\HttpClientException;
use Mailgun\Mailgun;

class MailgunDriver implements Driver
{

    /** @var Mailgun|null  */
    public $client = null;

    /** @var array */
    public $body = [];

    /** @var array */
    public $message = [];

    /** @var string */
    public $domain;

    /**
     * Driver constructor.
     * @param array $config
     * @throws InvalidConfiguration
     */
    public function __construct(array $config)
    {
        if (!isset($config['key'])) {
            throw InvalidConfiguration::invalidCredential('mailgun', 'key');
        }

        if (!isset($config['domain'])) {
            throw InvalidConfiguration::invalidCredential('mailgun', 'domain');
        }

        $this->client = Mailgun::create($config['key']);
        $this->domain = $config['domain'];
    }

    /**
     * @param string $name
     * @param string $email
     * @return Driver
     */
    public function setFrom(string $name, string $email): Driver
    {
        $this->message['from'] = "${name} <${email}>";

        return $this;
    }

    /**
     * @param string $template
     * @return Driver
     */
    public function setTemplate($template): Driver
    {
        $this->message['template'] = $template;

        return $this;
    }

    /**
     * @param string $subject
     * @return Driver
     */
    public function setSubject(string $subject): Driver
    {
        $this->message['subject'] = $subject;

        return $this;
    }

    /**
     * @param string $name
     * @param string $email
     * @return Driver
     */
    public function setRecipient(string $name, string $email): Driver
    {
        $this->message['to'] = "${name} <${email}>";

        return $this;
    }

    /**
     * @param array $variables
     * @return Driver
     */
    public function setVariables(array $variables): Driver
    {
        $this->message['h:X-Mailgun-Variables'] = json_encode($variables);

        return $this;
    }

    /**
     * @param string $language
     * @return Driver
     */
    public function setLanguage(string $language): Driver
    {
        return $this;
    }

    /**
     * @param string $file
     * @param string $name
     * @return Driver
     */
    public function addAttachment(string $file, string $name): Driver
    {
        $this->message['attachment'][] = [
            'fileContent' => base64_encode(file_get_contents($file)),
            'filename' => $name,
        ];

        return $this;
    }

    /**
     * @param bool $enable
     * @return Driver
     */
    public function trackClicks(bool $enable = false): Driver
    {
        $this->message['o:tracking-clicks'] = $enable ? 'yes' : 'no';

        return $this;
    }

    /**
     * @param bool $enable
     * @return Driver
     */
    public function trackOpens(bool $enable = false): Driver
    {
        $this->message['o:tracking-opens'] = $enable ? 'yes' : 'no';

        return $this;
    }

    /**
     * @return array
     * @throws SendError
     */
    public function send(): array
    {
        try {
            $response = $this->client->messages()->send(
                $this->domain,
                $this->message
            );

            return [
                'id' => $response->getId(),
                'message' => $response->getMessage(),
            ];
        } catch (HttpClientException $exception) {
            throw SendError::responseError('mailgun', 0, $exception);
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'body' => array_merge($this->body, [
                'Messages' => [
                    $this->message,
                ],
            ])
        ];
    }
}
