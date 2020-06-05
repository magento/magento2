<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    /**
     * @var AbstractBackend|MockObject
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = $this->getMockForAbstractClass(
            AbstractBackend::class,
            [],
            '',
            false
        );
    }

    public function testGetAffectedFields()
    {
        $valueId = 10;
        $attributeId = 42;

        $attribute = $this->createPartialMock(
            AbstractAttribute::class,
            ['getBackendTable', 'isStatic', 'getAttributeId', '__wakeup']
        );
        $attribute->expects($this->any())->method('getAttributeId')->willReturn($attributeId);

        $attribute->expects($this->any())->method('isStatic')->willReturn(false);

        $attribute->expects($this->any())->method('getBackendTable')->willReturn('table');

        $this->_model->setAttribute($attribute);

        $object = new DataObject();
        $this->_model->setValueId($valueId);

        $this->assertEquals(
            ['table' => [['value_id' => $valueId, 'attribute_id' => $attributeId]]],
            $this->_model->getAffectedFields($object)
        );
    }
}
