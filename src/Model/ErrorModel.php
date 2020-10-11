<?php

namespace Phalyfusion\Model;

use JsonSerializable;

/**
 * Class ErrorModel
 * Presenting error as fields that describe it.
 */
class ErrorModel implements JsonSerializable
{
    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $type;

    /**
     * Name of the plugin that generated this error.
     *
     * @var string
     */
    private $pluginName;

    /**
     * ErrorModel constructor.
     *
     * @param int    $line
     * @param string $message
     * @param string $type
     * @param string $pluginName
     */
    public function __construct(int $line, string $message, string $type, string $pluginName)
    {
        $this->setLine($line);
        $this->setMessage($message);
        $this->setType($type);
        $this->setPluginName($pluginName);
    }

    /**
     * @param int $line
     */
    public function setLine(int $line): void
    {
        $this->line = $line;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $pluginName
     */
    public function setPluginName(string $pluginName): void
    {
        $this->pluginName = $pluginName;
    }

    /**
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}