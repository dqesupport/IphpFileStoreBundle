<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation\Uploadable;
use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'photo')]
#[Uploadable]
class Photo
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Assert\Image(maxSize: '20M')]
    #[UploadableField(mapping: 'photo')]
    private $photo = null;

    #[Assert\Image(maxSize: '20M')]
    #[UploadableField(mapping: 'photo', fileDataProperty: 'photoInfo')]
    private $photoUpload = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private $photoInfo = null;

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

    public function setPhoto($photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
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

    public function getPhotoUpload()
    {
        return $this->photoUpload;
    }

    public function setPhotoUpload($photoUpload): void
    {
        $this->photoUpload = $photoUpload;
    }

    public function getPhotoInfo()
    {
        return $this->photoInfo;
    }

    public function setPhotoInfo($photoInfo): void
    {
        $this->photoInfo = $photoInfo;
    }
}
