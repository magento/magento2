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

namespace Magento\Catalog\Model;

use \Magento\Catalog\Api\Data\CategoryProductLinkInterface as ProductLink;

class CategoryLinkManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\CategoryLinkManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkBuilderMock;

    protected function setUp()
    {
        $this->categoryRepositoryMock = $this->getMock('\Magento\Catalog\Model\CategoryRepository', [], [], '', false);
        $this->productLinkBuilderMock = $this->getMock(
            '\Magento\Catalog\Api\Data\CategoryProductLinkDataBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Catalog\Model\CategoryLinkManagement(
            $this->categoryRepositoryMock,
            $this->productLinkBuilderMock
        );
    }

    public function testGetAssignedProducts()
    {
        $categoryId = 42;
        $productId = 55;
        $productsPosition = [$productId => 25];
        $productSku = 'testSku';
        $expectedValue = 'testComplete';
        $categoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            '',
            false
        );
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $items = [$productId => $productMock];
        $productLinkArray = [
            'sku' => $productSku,
            'position' => 25,
            'category_id' => $categoryId
        ];
        $productsMock = $this->getMock('\Magento\Framework\Data\Collection\Db', [], [], '', false);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productsPosition);
        $categoryMock->expects($this->once())->method('getProductCollection')->willReturn($productsMock);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $productsMock->expects($this->once())->method('getItems')->willReturn($items);
        $this->productLinkBuilderMock->expects($this->once())->method('populateWithArray')->with($productLinkArray)
            ->willReturnSelf();
        $this->productLinkBuilderMock->expects($this->once())->method('create')->willReturn($expectedValue);
        $this->assertEquals([$expectedValue], $this->model->getAssignedProducts($categoryId));
    }
}
