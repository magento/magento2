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
namespace Magento\Catalog\Service\V1\Category\ProductLinks;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Service\V1\Data\Category\ProductLink;
use Magento\Catalog\Service\V1\Data\Category\ProductLinkBuilder;
use Magento\TestFramework\Helper\ObjectManager;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Service\V1\Category\ProductLinks\ReadService
     */
    private $model;

    /**
     * @var Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category;

    /**
     * @var ProductLink|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLink;

    /**
     * @var ProductLinkBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLinkBuilder;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->setMethods(['getData', 'getId', 'load', '__wakeup', 'getProductsPosition', 'getProductCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Model\CategoryFactory|\PHPUnit_Framework_MockObject_MockObject $categoryFactory */
        $categoryFactory = $this->getMockBuilder('Magento\Catalog\Model\CategoryFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->category));

        $this->productLink = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Category\ProductLink')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productLinkBuilder = $this->getMockBuilder(
            'Magento\Catalog\Service\V1\Data\Category\ProductLinkBuilder'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->productLinkBuilder->expects($this->any())->method('create')
            ->will($this->returnValue($this->productLink));

        $this->model = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Category\ProductLinks\ReadService',
            [
                'categoryFactory' => $categoryFactory,
                'productLinkBuilder' => $this->productLinkBuilder
            ]
        );
    }

    public function testAssignedProducts()
    {
        $categoryId = 3;
        $productPosition = 1;
        $productId = $categoryId + 6;
        $productSku = "sku{$productId}";

        $productDto = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Category\ProductLink')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productObject */
        $productObject = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productObject->expects($this->once())->method('getSku')->will($this->returnValue($productSku));

        /** @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject $productCollection */
        $productCollection = $this->getMockBuilder('Magento\Framework\Data\Collection\Db')
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())->method('getItems')
            ->will($this->returnValue([$productId => $productObject]));

        $this->category->expects($this->once())->method('load')->with($this->equalTo($categoryId));
        $this->category->expects($this->once())->method('getId')->will($this->returnValue(333));
        $this->category->expects($this->once())->method('getProductsPosition')
            ->will($this->returnValue([$productId => $productPosition]));
        $this->category->expects($this->once())->method('getProductCollection')
            ->will($this->returnValue($productCollection));

        $this->productLinkBuilder->expects($this->any())->method('populateWithArray')->with(
            $this->equalTo(
                [
                    ProductLink::SKU => $productSku,
                    ProductLink::POSITION => $productPosition
                ]
            )
        )->will($this->returnValue($this->productLinkBuilder));

        $this->assertEquals([$productDto], $this->model->assignedProducts($categoryId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetCategoryNoSuchEntityException()
    {
        $categoryId = 3;
        $productId = $categoryId + 6;

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productObject */
        $productObject = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject $productCollection */
        $productCollection = $this->getMockBuilder('Magento\Framework\Data\Collection\Db')
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())->method('getItems')
            ->will($this->returnValue([$productId => $productObject]));

        $this->category->expects($this->once())->method('load')->with($this->equalTo($categoryId));
        $this->category->expects($this->once())->method('getId')->will($this->returnValue(0));

        $this->model->assignedProducts($categoryId);
    }
}
