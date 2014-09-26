<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Rss\Product;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NewProductsTest
 * @package Magento\Catalog\Model\Rss\Product
 */
class NewProductsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Rss\Product\NewProducts
     */
    protected $newProducts;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visibility;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezone;

    protected function setUp()
    {
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create']);
        $this->productFactory->expects($this->any())->method('create')->will($this->returnValue($this->product));
        $this->visibility = $this->getMock('Magento\Catalog\Model\Product\Visibility', [], [], '', false);
        $this->timezone = $this->getMock('Magento\Framework\Stdlib\DateTime\Timezone', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->newProducts = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Rss\Product\NewProducts',
            [
                'productFactory' => $this->productFactory,
                'visibility' => $this->visibility,
                'localeDate' => $this->timezone
            ]
        );
    }

    public function testGetProductsCollection()
    {
        /** @var \Magento\Framework\Stdlib\DateTime\Date|\PHPUnit_Framework_MockObject_MockObject $dateObject */
        $dateObject = $this->getMock('Magento\Framework\Stdlib\DateTime\Date');
        $dateObject->expects($this->any())->method('setTime')->will($this->returnSelf());
        $dateObject->expects($this->any())->method('toString')->will(
            $this->returnValue(date(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT))
        );
        $this->timezone->expects($this->exactly(2))->method('date')->will($this->returnValue($dateObject));

        /** @var \Magento\Catalog\Model\Resource\Product\Collection $productCollection */
        $productCollection = $this->getMock('Magento\Catalog\Model\Resource\Product\Collection', [], [], '', false);
        $this->product->expects($this->once())->method('getResourceCollection')->will(
            $this->returnValue($productCollection)
        );
        $storeId = 1;
        $productCollection->expects($this->once())->method('setStoreId')->with($storeId);
        $productCollection->expects($this->once())->method('addStoreFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('addAttributeToSort')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('applyFrontendPriceLimitations')->will($this->returnSelf());
        $visibleIds = [1, 3];
        $this->visibility->expects($this->once())->method('getVisibleInCatalogIds')->will(
            $this->returnValue($visibleIds)
        );
        $productCollection->expects($this->once())->method('setVisibility')->with($visibleIds)->will(
            $this->returnSelf()
        );

        $products = $this->newProducts->getProductsCollection($storeId);
        $this->assertEquals($productCollection, $products);
    }
}

