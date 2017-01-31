<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Backend;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend',
            [],
            '',
            false
        );
    }

    public function testGetAffectedFields()
    {
        $valueId = 10;
        $attributeId = 42;

        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getBackendTable', 'isStatic', 'getAttributeId', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->any())->method('getAttributeId')->will($this->returnValue($attributeId));

        $attribute->expects($this->any())->method('isStatic')->will($this->returnValue(false));

        $attribute->expects($this->any())->method('getBackendTable')->will($this->returnValue('table'));

        $this->_model->setAttribute($attribute);

        $object = new \Magento\Framework\DataObject();
        $this->_model->setValueId($valueId);

        $this->assertEquals(
            ['table' => [['value_id' => $valueId, 'attribute_id' => $attributeId]]],
            $this->_model->getAffectedFields($object)
        );
    }
}
