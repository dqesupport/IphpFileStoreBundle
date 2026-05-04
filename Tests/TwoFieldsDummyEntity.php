<?php

namespace Iphp\FileStoreBundle\Tests;

use Iphp\FileStoreBundle\Mapping\Annotation\Uploadable;
use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;

#[Uploadable]
class TwoFieldsDummyEntity
{
    #[UploadableField(mapping: 'dummy_file')]
    protected $file;

    #[UploadableField(mapping: 'dummy_image')]
    protected $image;

    public function getFile()
    {
        $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getImage()
    {
        $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
}
