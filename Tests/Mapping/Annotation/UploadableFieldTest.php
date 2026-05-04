<?php

namespace Iphp\FileStoreBundle\Tests\Mapping\Annotation;

use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;

class UploadableFieldTest extends \PHPUnit\Framework\TestCase
{
    private UploadableField $uploadableField;

    public function setUp(): void
    {
        $this->uploadableField = new UploadableField(mapping: 'dummy_file');
    }

    public function testExceptionThrownWhenNoMappingAttribute(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new UploadableField();
    }

    public function testGetMapping(): void
    {
        $this->assertSame('dummy_file', $this->uploadableField->getMapping());
    }

    public function testSetMapping(): void
    {
        $this->uploadableField->setMapping('other');
        $this->assertSame('other', $this->uploadableField->getMapping());
    }

    public function testGetSetFileUploadPropertyName(): void
    {
        $this->uploadableField->setFileUploadPropertyName('file');
        $this->assertSame('file', $this->uploadableField->getFileUploadPropertyName());
        $this->assertSame('file', $this->uploadableField->getFileDataPropertyName());
    }

    public function testGetSetFileDataPropertyName(): void
    {
        $this->uploadableField->setFileUploadPropertyName('file');
        $this->uploadableField->setFileDataPropertyName('file_data');

        $this->assertSame('file', $this->uploadableField->getFileUploadPropertyName());
        $this->assertSame('file_data', $this->uploadableField->getFileDataPropertyName());
    }

    public function testFileDataPropertyFromConstructor(): void
    {
        $field = new UploadableField(mapping: 'dummy_file', fileDataProperty: 'file_data');
        $field->setFileUploadPropertyName('file');

        $this->assertSame('file_data', $field->getFileDataPropertyName());
    }
}
