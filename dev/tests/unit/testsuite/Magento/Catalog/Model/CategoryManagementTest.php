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

class CategoryManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\CategoryManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryTreeMock;

    protected function setUp()
    {
        $this->categoryRepositoryMock = $this->getMock('\Magento\Catalog\Api\CategoryRepositoryInterface');
        $this->categoryTreeMock = $this->getMock('\Magento\Catalog\Model\Category\Tree', [], [], '', false);
        $this->model = new \Magento\Catalog\Model\CategoryManagement(
            $this->categoryRepositoryMock,
            $this->categoryTreeMock
        );
    }

    public function testGetTree()
    {
        $rootCategoryId = 1;
        $depth = 2;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);
        $nodeMock = $this->getMock('\Magento\Framework\Data\Tree\Node', [], [], '', false);

        $this->categoryRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($rootCategoryId)
            ->willReturn($categoryMock);
        $this->categoryTreeMock
            ->expects($this->once())
            ->method('getRootNode')
            ->with($categoryMock)
            ->willReturn($nodeMock);
        $this->categoryTreeMock
            ->expects($this->once())
            ->method('getTree')
            ->with($nodeMock, $depth)
            ->willReturn('expected');
        $this->assertEquals(
            'expected',
            $this->model->getTree($rootCategoryId, $depth)
        );
    }

    public function testGetTreeWithNullArguments()
    {
        $rootCategoryId = null;
        $depth = null;
        $category = null;

        $this->categoryRepositoryMock->expects($this->never())->method('get');
        $this->categoryTreeMock->expects($this->once())->method('getRootNode')->with($category)->willReturn(null);
        $this->categoryTreeMock->expects($this->exactly(2))->method('getTree')->with($category, $depth);
        $this->assertEquals(
            $this->model->getTree($rootCategoryId, $depth),
            $this->categoryTreeMock->getTree(null, null)
        );
    }

    public function testMove()
    {
        $categoryId = 2;
        $parentId = 1;
        $afterId = null;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], 'categoryMock', false);
        $parentCategoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            'parentCategoryMock',
            false
        );

        $this->categoryRepositoryMock
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap(
                [
                    [$categoryId, $categoryMock],
                    [$parentId, $parentCategoryMock]
                ]
            ));
        $parentCategoryMock->expects($this->once())->method('hasChildren')->willReturn(true);
        $parentCategoryMock->expects($this->once())->method('getChildren')->willReturn('5,6,7');
        $categoryMock->expects($this->once())->method('getPath');
        $parentCategoryMock->expects($this->once())->method('getPath');
        $categoryMock->expects($this->once())->method('move')->with($parentId, '7');
        $this->assertTrue($this->model->move($categoryId, $parentId, $afterId));
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Operation do not allow to move a parent category to any of children category
     */
    public function testMoveWithException()
    {
        $categoryId = 2;
        $parentId = 1;
        $afterId = null;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], 'categoryMock', false);
        $parentCategoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            'parentCategoryMock',
            false
        );

        $this->categoryRepositoryMock
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap(
                [
                    [$categoryId, $categoryMock],
                    [$parentId, $parentCategoryMock]
                ]
            ));
        $categoryMock->expects($this->once())->method('getPath')->willReturn('test');
        $parentCategoryMock->expects($this->once())->method('getPath')->willReturn('test');
        $this->model->move($categoryId, $parentId, $afterId);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Could not move category
     */
    public function testMoveWithCouldNotMoveException()
    {
        $categoryId = 2;
        $parentId = 1;
        $afterId = null;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], 'categoryMock', false);
        $parentCategoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            'parentCategoryMock',
            false
        );

        $this->categoryRepositoryMock
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap(
                [
                    [$categoryId, $categoryMock],
                    [$parentId, $parentCategoryMock]
                ]
            ));
        $categoryMock->expects($this->once())
            ->method('move')
            ->with($parentId, $afterId)
            ->willThrowException(new \Magento\Framework\Model\Exception);
        $this->model->move($categoryId, $parentId, $afterId);
    }
}
