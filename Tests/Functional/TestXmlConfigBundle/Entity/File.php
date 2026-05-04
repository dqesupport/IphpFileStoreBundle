<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity;

class File extends UploadableEntity
{
    private ?int $id = null;

    private ?string $title = null;

    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }
}
