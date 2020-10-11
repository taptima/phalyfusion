<?php

namespace Phalyfusion\Model;

use JsonSerializable;

/**
 * Class FileModel
 * Presenting error list for a file as an ErrorModel array.
 */
class FileModel implements JsonSerializable
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var ErrorModel[]
     */
    private $errors = [];

    /**
     * FileModel constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param ErrorModel[] $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return ErrorModel[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param ErrorModel $errorModel
     */
    public function appendError(ErrorModel $errorModel): void
    {
        $this->errors[] = $errorModel;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}