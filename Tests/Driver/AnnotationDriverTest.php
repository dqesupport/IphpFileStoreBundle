<?php

namespace Iphp\FileStoreBundle\Tests\Driver;

use Iphp\FileStoreBundle\Driver\AnnotationDriver;
use Iphp\FileStoreBundle\Mapping\Annotation\Uploadable;
use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;
use Iphp\FileStoreBundle\Tests\ChildOfDummyEntity;
use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Tests\TwoFieldsDummyEntity;

class AnnotationDriverTest extends \PHPUnit\Framework\TestCase
{
    private AnnotationDriver $driver;

    protected function setUp(): void
    {
        $this->driver = new AnnotationDriver();
    }

    public function testReadUploadableAnnotation(): void
    {
        $annot = $this->driver->readUploadable(new \ReflectionClass(DummyEntity::class));

        $this->assertInstanceOf(Uploadable::class, $annot);
    }

    public function testReadUploadableAnnotationFromParent(): void
    {
        $annot = $this->driver->readUploadable(new \ReflectionClass(ChildOfDummyEntity::class));

        $this->assertInstanceOf(Uploadable::class, $annot);
    }

    public function testReadUploadableAnnotationReturnsNullWhenNonePresent(): void
    {
        $annot = $this->driver->readUploadable(new \ReflectionClass(\stdClass::class));

        $this->assertNull($annot);
    }

    public function testReadOneUploadableField(): void
    {
        $fields = $this->driver->readUploadableFields(new \ReflectionClass(DummyEntity::class));

        $this->assertCount(1, $fields);
        $this->assertInstanceOf(UploadableField::class, $fields[0]);
        $this->assertSame('file', $fields[0]->getFileUploadPropertyName());
        $this->assertSame('dummy_file', $fields[0]->getMapping());
    }

    public function testReadOneUploadableFieldFromParent(): void
    {
        $fields = $this->driver->readUploadableFields(new \ReflectionClass(ChildOfDummyEntity::class));

        $this->assertCount(1, $fields);
        $this->assertSame('file', $fields[0]->getFileUploadPropertyName());
    }

    public function testReadUploadableFieldSingle(): void
    {
        $field = $this->driver->readUploadableField(new \ReflectionClass(DummyEntity::class), 'file');

        $this->assertInstanceOf(UploadableField::class, $field);
        $this->assertSame('file', $field->getFileUploadPropertyName());
        $this->assertSame('dummy_file', $field->getMapping());
    }

    public function testReadUploadableFieldNoMapping(): void
    {
        $field = $this->driver->readUploadableField(new \ReflectionClass(DummyEntity::class), 'title');

        $this->assertNull($field);
    }

    public function testReadUploadableFieldOnMissingProperty(): void
    {
        $field = $this->driver->readUploadableField(new \ReflectionClass(DummyEntity::class), 'doesNotExist');

        $this->assertNull($field);
    }

    public function testReadTwoUploadableFields(): void
    {
        $fields = $this->driver->readUploadableFields(new \ReflectionClass(TwoFieldsDummyEntity::class));

        $this->assertCount(2, $fields);
        $names = array_map(fn(UploadableField $f) => $f->getFileUploadPropertyName(), $fields);
        $this->assertContains('file', $names);
        $this->assertContains('image', $names);
    }

    public function testReadNoUploadableFieldsWhenNoneExist(): void
    {
        $fields = $this->driver->readUploadableFields(new \ReflectionClass(\stdClass::class));

        $this->assertSame([], $fields);
    }
}
