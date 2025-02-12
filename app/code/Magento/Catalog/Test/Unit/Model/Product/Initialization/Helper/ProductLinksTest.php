<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Initialization\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductLinksTest extends TestCase
{
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
        $mockBuilder = $this->getMockBuilder(Product::class)
            ->addMethods(
                [
                    'getRelatedReadonly',
                    'getUpsellReadonly',
                    'getCrosssellReadonly',
                    'setCrossSellLinkData',
                    'setUpSellLinkData',
                    'setRelatedLinkData',
                ]
            )
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getRelatedReadonly')
            ->willReturn(false);

        $mock->expects($this->any())
            ->method('getUpsellReadonly')
            ->willReturn(false);

        $mock->expects($this->any())
            ->method('getCrosssellReadonly')
            ->willReturn(false);

        $mock->expects($this->any())
            ->method('setCrossSellLinkData');

        $mock->expects($this->any())
            ->method('setUpSellLinkData');

        $mock->expects($this->any())
            ->method('setRelatedLinkData');

        return $mock;
    }
}
