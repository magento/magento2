<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product\Initialization\Helper;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\ObjectManager;

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
            '\Magento\Catalog\Model\Product',
            $this->model->initializeLinks($this->getMockedProduct(), $links)
        );
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject('Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks');
    }

    /**
     * @return Product
     */
    private function getMockedProduct()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Product')
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
