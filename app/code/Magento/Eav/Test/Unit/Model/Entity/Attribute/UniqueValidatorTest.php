<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

use PHPUnit\Framework\TestCase;
use Magento\Eav\Model\Entity\Attribute\UniqueValidator;
use Magento\Framework\DataObject;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class UniqueValidatorTest extends TestCase
{
    /**
     * @var UniqueValidator
     */
    private $uniqueValidator;

    protected function setUp(): void
    {
        $this->uniqueValidator = new UniqueValidator();
    }

    public function testValidate()
    {
        $attribute = $this->createMock(AbstractAttribute::class);
        $object = $this->createMock(DataObject::class);
        $entity = $this->createMock(AbstractEntity::class);
        $entityLinkField = 'entityLinkField';
        $entityIds = [1, 2, 3];

        $object->expects($this->once())
            ->method('getData')
            ->with($entityLinkField)
            ->willReturn(2);

        $this->assertTrue($this->uniqueValidator->validate($attribute, $object, $entity, $entityLinkField, $entityIds));
    }
}
