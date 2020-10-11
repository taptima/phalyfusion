<?php

namespace Phalyfusion\Model;

use JsonSerializable;

/**
 * Class PluginOutputModel
 * Model presenting output of the plugin as FileModel for file path.
 */
class PluginOutputModel implements JsonSerializable
{
    /**
     * $files = ['<fileName>' => FileModel].
     *
     * @var FileModel[]
     */
    private $files = [];

    /**
     * $files = ['<fileName>' => FileModel].
     *
     * @param FileModel[] $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * $files = ['<fileName>' => FileModel].
     *
     * @return FileModel[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param string     $filePath
     * @param ErrorModel $errorModel
     */
    public function appendError(string $filePath, ErrorModel $errorModel): void
    {
        $this->appendFileIfNotExists($filePath);
        $this->files[$filePath]->appendError($errorModel);
    }

    /**
     * @param string $filePath
     */
    public function appendFileIfNotExists(string $filePath): void
    {
        if (!array_key_exists($filePath, $this->files)) {
            $this->files[$filePath] = new FileModel($filePath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}