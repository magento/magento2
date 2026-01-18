<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Initialization\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductLinksTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ProductLinks
     */
    private $model;

    public function testInitializeLinks()
    {
        $links = ['related' => ['data'], 'upsell' => ['data'], 'crosssell' => ['data']];
        $this->assertInstanceOf(
            Product::class,
            $this->model->initializeLinks($this->getMockedProduct(), $links)
        );
    }

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(ProductLinks::class);
    }

    /**
     * @return Product
     */
    private function getMockedProduct()
    {
        $mock = $this->createPartialMockWithReflection(
            Product::class,
            ['setRelatedLinkData', 'setUpSellLinkData', 'setCrossSellLinkData']
        );
        $mock->method('setRelatedLinkData')->willReturnSelf();
        $mock->method('setUpSellLinkData')->willReturnSelf();
        $mock->method('setCrossSellLinkData')->willReturnSelf();

        return $mock;
    }
}
