<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Pricing\Renderer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalableResolverTest extends TestCase
{
    /**
     * @var SalableResolver
     */
    protected $object;

    /**
     * @var Product|MockObject
     */
    protected $product;

    protected function setUp(): void
    {
        $this->product = $this->createPartialMock(Product::class, []);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            SalableResolver::class
        );
    }

    public function testSalableItem()
    {
        $this->product->setData('can_show_price', true);

        $result = $this->object->isSalable($this->product);
        $this->assertTrue($result);
    }

    public function testNotSalableItem()
    {
        $this->product->setData('can_show_price', false);

        $result = $this->object->isSalable($this->product);
        $this->assertFalse($result);
    }
}
