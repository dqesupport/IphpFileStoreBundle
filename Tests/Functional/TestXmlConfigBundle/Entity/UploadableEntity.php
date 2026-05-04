<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity;

use Iphp\FileStoreBundle\Mapping\Annotation\Uploadable;
use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;
use Symfony\Component\Validator\Constraints as Assert;

#[Uploadable]
abstract class UploadableEntity
{
    #[Assert\Image(maxSize: '20M')]
    #[UploadableField(mapping: 'file')]
    protected $file = null;

    public function setFile($file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }
}
