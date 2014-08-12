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

use Magento\TestFramework\Helper\ObjectManager;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Service\V1\Category\ProductLinks\WriteService
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category;

    /**
     * @var \Magento\Catalog\Service\V1\Category\CategoryLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryLoader;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Category\ProductLink|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLink;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->productLink = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Category\ProductLink')
            ->setMethods(['getSku', 'getPosition'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->setMethods(['getProductsPosition', 'setPostedProducts', 'save', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryLoader = $this->getMockBuilder('Magento\Catalog\Service\V1\Category\CategoryLoader')
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $categoryLoaderFactory = $this->getMockBuilder('Magento\Catalog\Service\V1\Category\CategoryLoaderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryLoaderFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->categoryLoader));

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productFactory = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $productFactory->expects($this->any())->method('create')->will($this->returnValue($this->product));

        $this->model = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Category\ProductLinks\WriteService',
            [
                'categoryLoaderFactory' => $categoryLoaderFactory,
                'productFactory' => $productFactory
            ]
        );
    }

    public function testAssignProduct()
    {
        $categoryId = 33;

        $this->prepareMocksForAssign($categoryId);
        $this->category->expects($this->once())->method('save');

        $this->assertTrue($this->model->assignProduct($categoryId, $this->productLink));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAssignProductCouldNotSaveException()
    {
        $categoryId = 33;

        $this->prepareMocksForAssign($categoryId);
        $this->category->expects($this->once())->method('save')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception();
                    }
                )
            );

        $this->assertTrue($this->model->assignProduct($categoryId, $this->productLink));
    }

    public function testRemoveProduct()
    {
        $categoryId = 33;
        $productId = 333;

        $this->prepareMocksForRemove($categoryId, $productId);
        $this->category->expects($this->once())->method('save');

        $this->model->removeProduct($categoryId, 'sku333');
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testRemoveProductCouldNotSaveException()
    {
        $categoryId = 33;
        $productId = 333;

        $this->prepareMocksForRemove($categoryId, $productId);
        $this->category->expects($this->once())->method('save')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception();
                    }
                )
            );

        $this->model->removeProduct($categoryId, 'sku333');
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testRemoveProductStateException()
    {
        $categoryId = 33;
        $productId = 33;

        $this->prepareMocksForRemove($categoryId, $productId);

        $this->model->removeProduct($categoryId, 'sku333');
    }

    private function prepareMocksForRemove($categoryId, $productId)
    {
        $productSku = 'sku333';
        $productsPosition = [105 => 16, 333 => 1];

        $this->categoryLoader->expects($this->once())->method('load')
            ->with($this->equalTo($categoryId))
            ->will($this->returnValue($this->category));

        $this->category->expects($this->once())->method('getProductsPosition')
            ->will($this->returnValue($productsPosition));
        unset($productsPosition[$productId]);
        $this->category->expects($this->any())->method('setPostedProducts')
            ->with($this->equalTo($productsPosition));

        $this->product->expects($this->once())->method('getIdBySku')->with($this->equalTo($productSku))
            ->will($this->returnValue($productId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testAssignStateException()
    {
        $categoryId = 33;

        $this->prepareMocksForAssign($categoryId, 334);

        $this->assertTrue($this->model->assignProduct($categoryId, $this->productLink));
    }

    public function testUpdateProduct()
    {
        $categoryId = 33;

        $this->prepareMocksForAssign($categoryId, 334);
        $this->category->expects($this->once())->method('save');

        $this->assertTrue($this->model->updateProduct($categoryId, $this->productLink));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testUpdateProductCouldNotSaveException()
    {
        $categoryId = 33;

        $this->prepareMocksForAssign($categoryId, 334);
        $this->category->expects($this->once())->method('save')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception();
                    }
                )
            );

        $this->assertTrue($this->model->updateProduct($categoryId, $this->productLink));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testUpdateStateException()
    {
        $categoryId = 33;

        $this->prepareMocksForAssign($categoryId);

        $this->assertTrue($this->model->updateProduct($categoryId, $this->productLink));
    }

    private function prepareMocksForAssign($categoryId, $productId = 333)
    {
        $productSku = 'sku333';
        $productsPosition = [105 => 16, 334 => 1];

        $this->productLink->expects($this->once())->method('getSku')->will($this->returnValue($productSku));
        $this->productLink->expects($this->any())->method('getPosition')->will($this->returnValue($categoryId));

        $this->categoryLoader->expects($this->once())->method('load')
            ->with($this->equalTo($categoryId))
            ->will($this->returnValue($this->category));

        $this->category->expects($this->once())->method('getProductsPosition')
            ->will($this->returnValue($productsPosition));
        $newProductPositions = [$productId => $categoryId] + $productsPosition;
        $this->category->expects($this->any())->method('setPostedProducts')
            ->with($this->equalTo($newProductPositions));

        $this->product->expects($this->once())->method('getIdBySku')->with($this->equalTo($productSku))
            ->will($this->returnValue($productId));
    }
}
