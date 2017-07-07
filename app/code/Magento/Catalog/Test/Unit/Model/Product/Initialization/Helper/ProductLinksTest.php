<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Initialization\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductLinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks
     */
    private $model;

    public function testInitializeLinks()
    {
        $links = ['related' => ['data'], 'upsell' => ['data'], 'crosssell' => ['data']];
        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product::class,
            $this->model->initializeLinks($this->getMockedProduct(), $links)
        );
    }

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(\Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks::class);
    }

    /**
     * @return Product
     */
    private function getMockedProduct()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getRelatedReadonly',
                    'getUpsellReadonly',
                    'getCrosssellReadonly',
                    'setCrossSellLinkData',
                    'setUpSellLinkData',
                    'setRelatedLinkData',
                    '__wakeup',
                ]
            )
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getRelatedReadonly')
            ->will($this->returnValue(false));

        $mock->expects($this->any())
            ->method('getUpsellReadonly')
            ->will($this->returnValue(false));

        $mock->expects($this->any())
            ->method('getCrosssellReadonly')
            ->will($this->returnValue(false));

        $mock->expects($this->any())
            ->method('setCrossSellLinkData');

        $mock->expects($this->any())
            ->method('setUpSellLinkData');

        $mock->expects($this->any())
            ->method('setRelatedLinkData');

        return $mock;
    }
}
