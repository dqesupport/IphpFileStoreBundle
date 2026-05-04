<?php

namespace Iphp\FileStoreBundle\Mapping\Annotation;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UploadableField
{
    private string $mapping;

    private ?string $fileUploadPropertyName = null;

    private ?string $fileDataPropertyName;

    public function __construct(string $mapping, ?string $fileDataProperty = null)
    {
        $this->mapping = $mapping;
        $this->fileDataPropertyName = $fileDataProperty;
    }

    public function getMapping(): string
    {
        return $this->mapping;
    }

    public function setMapping(string $mapping): void
    {
        $this->mapping = $mapping;
    }

    public function getFileUploadPropertyName(): ?string
    {
        return $this->fileUploadPropertyName;
    }

    public function setFileUploadPropertyName(string $propertyName): void
    {
        $this->fileUploadPropertyName = $propertyName;
    }

    public function getFileDataPropertyName(): ?string
    {
        return $this->fileDataPropertyName ?: $this->fileUploadPropertyName;
    }

    public function setFileDataPropertyName(string $fileDataPropertyName): void
    {
        $this->fileDataPropertyName = $fileDataPropertyName;
    }
}
