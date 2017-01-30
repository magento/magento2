<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Unit\Model\Form\Element;

class AbstractSwatchTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Swatches\Model\Form\Element\AbstractSwatch|\PHPUnit_Framework_MockObject_MockObject */
    private $swatch;

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attribute;

    /** @var \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource|\PHPUnit_Framework_MockObject_MockObject */
    private $source;

    protected function setUp()
    {
        $this->source = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Source\AbstractSource')
            ->getMockForAbstractClass();

        $this->attribute = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->disableOriginalConstructor()
            ->getMock();

        $this->swatch = $this->getMockBuilder('Magento\Swatches\Model\Form\Element\AbstractSwatch')
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
    }

    public function testGetValues()
    {
        $expected = [1, 2, 3];

        $this->source->expects($this->once())->method('getAllOptions')
            ->with(true, true)
            ->willReturn($expected);
        $this->attribute->expects($this->once())->method('getSource')
            ->willReturn($this->source);
        $this->swatch->expects($this->once())->method('getData')
            ->with('entity_attribute')
            ->willReturn($this->attribute);

        $method = new \ReflectionMethod('Magento\Swatches\Model\Form\Element\AbstractSwatch', 'getValues');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->swatch));
    }

    public function testGetValuesEmpty()
    {
        $this->swatch->expects($this->once())->method('getData')
            ->with('entity_attribute')
            ->willReturn(null);

        $method = new \ReflectionMethod('Magento\Swatches\Model\Form\Element\AbstractSwatch', 'getValues');
        $method->setAccessible(true);

        $this->assertEmpty($method->invoke($this->swatch));
    }
}
