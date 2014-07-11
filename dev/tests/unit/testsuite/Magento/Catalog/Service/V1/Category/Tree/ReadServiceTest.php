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

namespace Magento\Catalog\Service\V1\Category\Tree;

/**
 * Test for \Magento\Catalog\Service\V1\Category\Tree\ReadService
 */
class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Service\V1\Data\Category\Tree
     */
    protected $categoryTreeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactoryMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Service\V1\Category\Tree\ReadService
     */
    protected $categoryService;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->categoryTreeMock = $this->getMockBuilder(
                'Magento\Catalog\Service\V1\Data\Category\Tree'
            )->disableOriginalConstructor()
            ->getMock();

        $this->categoryFactoryMock = $this->getMockBuilder(
                '\Magento\Catalog\Model\CategoryFactory'
            )->disableOriginalConstructor()
            ->setMethods(['create', 'load'])
            ->getMock();

        $this->categoryService = $this->objectManager
            ->getObject(
                '\Magento\Catalog\Service\V1\Category\Tree\ReadService',
                [
                    'categoryFactory' => $this->categoryFactoryMock,
                    'categoryTree' => $this->categoryTreeMock,
                ]
            );

    }

    /**
     * @dataProvider treeDataProvider
     */
    public function testTree($rootCategoryId, $depth)
    {
        $rootNode = $this->getMockBuilder(
            'Magento\Framework\Data\Tree\Node'
        )->disableOriginalConstructor()
        ->getMock();

        $category = null;
        if (!is_null($rootCategoryId)) {
            $category = $this->getMockBuilder(
                '\Magento\Catalog\Model\Category'
            )->disableOriginalConstructor()
            ->getMock();

            $category->expects($this->once())->method('getId')->will($this->returnValue($rootCategoryId));

            $this->categoryFactoryMock->expects($this->once())->method('create')->will($this->returnSelf());
            $this->categoryFactoryMock->expects($this->once())->method('load')
                ->with($this->equalTo($rootCategoryId))
                ->will($this->returnValue($category));
        }
        $this->categoryTreeMock->expects($this->once())->method('getRootNode')
            ->with($this->equalTo($category))
            ->will($this->returnValue($rootNode));

        $this->categoryTreeMock->expects($this->once())->method('getTree')
            ->with($this->equalTo($rootNode), $this->equalTo($depth));
        $this->categoryService->tree($rootCategoryId, $depth);
    }

    /**
     * @return array
     */
    public function treeDataProvider()
    {
        return array(
            [1, 0],
            [null, 3]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testTreeAbsentCategory()
    {
        $category = $this->getMockBuilder(
            '\Magento\Catalog\Model\Category'
        )->disableOriginalConstructor()
        ->getMock();

        $category->expects($this->once())->method('getId')->will($this->returnValue(null));
        $category->expects($this->never())->method('getPathIds');

        $this->categoryFactoryMock->expects($this->once())->method('create')->will($this->returnSelf());
        $this->categoryFactoryMock->expects($this->once())->method('load')
            ->with($this->equalTo(1))
            ->will($this->returnValue($category));

        $this->categoryService->tree(1);
    }
}
