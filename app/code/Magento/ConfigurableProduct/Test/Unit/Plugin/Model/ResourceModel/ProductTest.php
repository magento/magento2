<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeSaveConfigurable()
    {
        $subject = $this->getMock('Magento\Catalog\Model\ResourceModel\Product', [], [], '', false);
        $object = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId', 'getTypeInstance'], [], '', false);
        $type = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            ['getSetAttributes'],
            [],
            '',
            false
        );
        $type->expects($this->once())->method('getSetAttributes')->with($object);

        $object->expects($this->once())->method('getTypeId')->will($this->returnValue(Configurable::TYPE_CODE));
        $object->expects($this->once())->method('getTypeInstance')->will($this->returnValue($type));

        $product = new \Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product();
        $product->beforeSave(
            $subject,
            $object
        );
    }

    public function testBeforeSaveSimple()
    {
        $subject = $this->getMock('Magento\Catalog\Model\ResourceModel\Product', [], [], '', false);
        $object = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId', 'getTypeInstance'], [], '', false);
        $object->expects($this->once())->method('getTypeId')->will($this->returnValue(Type::TYPE_SIMPLE));
        $object->expects($this->never())->method('getTypeInstance');

        $product = new \Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product();
        $product->beforeSave(
            $subject,
            $object
        );
    }
}
