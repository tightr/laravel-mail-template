<?php


namespace Tightr\MailTemplate\Drivers;

/**
 * Interface Driver
 * @package Tightr\MailTemplate\Drivers
 */
interface Driver
{
    /**
     * Driver constructor.
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * @param string $name
     * @param string $email
     * @return Driver
     */
    public function setFrom(string $name, string $email): self;

    /**
     * @param mixed $template
     * @return Driver
     */
    public function setTemplate($template): self;

    /**
     * @param string $email
     * @param mixed $name
     * @return Driver
     */
    public function setTemplateErrorReporting(string $email, $name): self;

    /**
     * @param string $subject
     * @return Driver
     */
    public function setSubject(string $subject): self;

    /**
     * @param string $name
     * @param string $email
     * @return Driver
     */
    public function setRecipient(string $name, string $email): self;

    /**
     * @param array $variables
     * @return Driver
     */
    public function setVariables(array $variables): self;

    /**
     * @param string $language
     * @return Driver
     */
    public function setLanguage(string $language): self;

    /**
     * @param string $file
     * @param string $name
     * @return Driver
     */
    public function addAttachment(string $file, string $name): self;

    /**
     * @param bool $enable
     * @return Driver
     */
    public function trackClicks(bool $enable = false): self;

    /**
     * @param bool $enable
     * @return Driver
     */
    public function trackOpens(bool $enable = false): self;

    /**
     * @return array
     */
    public function send(): array;

    /**
     * @return array
     */
    public function toArray(): array;
}
