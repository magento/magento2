<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model\Form\Element;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Swatches\Model\Form\Element\AbstractSwatch;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractSwatchTest extends TestCase
{
    /** @var AbstractSwatch|MockObject */
    private $swatch;

    /** @var Attribute|MockObject */
    private $attribute;

    /** @var AbstractSource|MockObject */
    private $source;

    protected function setUp(): void
    {
        $this->source = $this->getMockBuilder(AbstractSource::class)
            ->getMockForAbstractClass();

        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->swatch = $this->getMockBuilder(AbstractSwatch::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
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

        $method = new \ReflectionMethod(AbstractSwatch::class, 'getValues');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->swatch));
    }

    public function testGetValuesEmpty()
    {
        $this->swatch->expects($this->once())->method('getData')
            ->with('entity_attribute')
            ->willReturn(null);

        $method = new \ReflectionMethod(AbstractSwatch::class, 'getValues');
        $method->setAccessible(true);

        $this->assertEmpty($method->invoke($this->swatch));
    }
}
