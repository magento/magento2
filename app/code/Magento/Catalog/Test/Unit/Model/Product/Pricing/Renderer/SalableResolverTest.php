<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Pricing\Renderer;

class SalableResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver
     */
    protected $object;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    protected function setUp()
    {
        $this->product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'getCanShowPrice', 'isSalable'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            'Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver'
        );
    }

    public function testSalableItem()
    {
        $this->product->expects($this->any())
            ->method('getCanShowPrice')
            ->willReturn(true);

        $this->product->expects($this->any())->method('isSalable')->willReturn(true);

        $result = $this->object->isSalable($this->product);
        $this->assertTrue($result);
    }

    public function testNotSalableItem()
    {
        $this->product->expects($this->any())
            ->method('getCanShowPrice')
            ->willReturn(true);

        $this->product->expects($this->any())->method('isSalable')->willReturn(false);

        $result = $this->object->isSalable($this->product);
        $this->assertFalse($result);
    }
}
