<?php

namespace Iphp\FileStoreBundle\Driver;

use Iphp\FileStoreBundle\Mapping\Annotation\Uploadable;
use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;

class AnnotationDriver
{
    /** @var array<string, Uploadable|null> */
    protected array $uploadedClass = [];

    public function readUploadable(\ReflectionClass $class): ?Uploadable
    {
        $baseClassName = $class->getName();

        if (array_key_exists($baseClassName, $this->uploadedClass)) {
            return $this->uploadedClass[$baseClassName];
        }

        $current = $class;
        do {
            $attributes = $current->getAttributes(Uploadable::class);
            if ($attributes) {
                $instance = $attributes[0]->newInstance();
                $this->uploadedClass[$baseClassName] = $instance;
                return $instance;
            }
            $current = $current->getParentClass();
        } while ($current);

        return $this->uploadedClass[$baseClassName] = null;
    }

    /**
     * @return UploadableField[]
     */
    public function readUploadableFields(\ReflectionClass $class): array
    {
        $fields = [];
        foreach ($class->getProperties() as $property) {
            $field = $this->readPropertyField($property);
            if ($field !== null) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    public function readUploadableField(\ReflectionClass $class, string $field): ?UploadableField
    {
        try {
            $property = $class->getProperty($field);
        } catch (\ReflectionException) {
            return null;
        }

        return $this->readPropertyField($property);
    }

    private function readPropertyField(\ReflectionProperty $property): ?UploadableField
    {
        $attributes = $property->getAttributes(UploadableField::class);
        if (!$attributes) {
            return null;
        }

        $field = $attributes[0]->newInstance();
        $field->setFileUploadPropertyName($property->getName());

        return $field;
    }
}
