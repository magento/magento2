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

namespace Magento\Catalog\Service\V1\Data\Category;

/**
 * Test for \Magento\Catalog\Service\V1\Category\ReadService
 */
class TreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\Resource\Category\Tree
     */
    protected $categoryTreeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\Resource\Category\Collection
     */
    protected $categoryCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Service\V1\Data\Eav\Category\TreeBuilderFactory
     */
    protected $treeBuilderFactory;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Category\Tree
     */
    protected $tree;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->categoryTreeMock = $this->getMockBuilder(
                '\Magento\Catalog\Model\Resource\Category\Tree'
            )->disableOriginalConstructor()
            ->getMock();

        $this->categoryCollection = $this->getMockBuilder(
                '\Magento\Catalog\Model\Resource\Category\Collection'
            )->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(
                '\Magento\Framework\StoreManagerInterface'
            )->disableOriginalConstructor()
            ->getMock();

        $this->treeBuilderFactory = $this->getMockBuilder(
                '\Magento\Catalog\Service\V1\Data\Eav\Category\TreeBuilderFactory'
            )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->tree = $this->objectManager
            ->getObject(
                'Magento\Catalog\Service\V1\Data\Category\Tree',
                [
                    'categoryCollection' => $this->categoryCollection,
                    'categoryTree' => $this->categoryTreeMock,
                    'storeManager' => $this->storeManagerMock,
                    'treeBuilderFactory' => $this->treeBuilderFactory
                ]
            );

    }

    public function testGetNode()
    {
        $category = $this->getMockBuilder(
                '\Magento\Catalog\Model\Category'
            )->disableOriginalConstructor()
            ->getMock();
        $category->expects($this->exactly(2))->method('getId')->will($this->returnValue(1));

        $node = $this->getMockBuilder(
                '\Magento\Framework\Data\Tree\Node'
            )->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->once())->method('loadChildren');
        $this->categoryTreeMock->expects($this->once())->method('loadNode')
            ->with($this->equalTo(1))
            ->will($this->returnValue($node));

        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $store->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->categoryCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $this->categoryCollection->expects($this->once())->method('setProductStoreId')->will($this->returnSelf());
        $this->categoryCollection->expects($this->once())->method('setLoadProductCount')->will($this->returnSelf());
        $this->categoryCollection->expects($this->once())->method('setStoreId')->will($this->returnSelf());

        $this->categoryTreeMock->expects($this->once())->method('addCollectionData')
            ->with($this->equalTo($this->categoryCollection));
        $this->tree->getRootNode($category);
    }

    public function testGetRootNode()
    {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $store->expects($this->once())->method('getRootCategoryId')->will($this->returnValue(2));
        $store->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->categoryCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $this->categoryCollection->expects($this->once())->method('setProductStoreId')->will($this->returnSelf());
        $this->categoryCollection->expects($this->once())->method('setLoadProductCount')->will($this->returnSelf());
        $this->categoryCollection->expects($this->once())->method('setStoreId')->will($this->returnSelf());

        $node = $this->getMockBuilder(
            'Magento\Catalog\Model\Resource\Category\Tree'
        )->disableOriginalConstructor()
        ->getMock();
        $node->expects($this->once())->method('addCollectionData')
            ->with($this->equalTo($this->categoryCollection));
        $node->expects($this->once())->method('getNodeById')->with($this->equalTo(2));
        $this->categoryTreeMock->expects($this->once())->method('load')
            ->with($this->equalTo(null))
            ->will($this->returnValue($node));
        $this->tree->getRootNode();
    }

    public function testGetTree()
    {
        $depth = 2;
        $currentLevel = 1;

        $builder = $this->getMockBuilder('\Magento\Catalog\Service\V1\Data\Eav\Category\TreeBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->any())->method('setId')->with($this->equalTo($currentLevel))
            ->will($this->returnSelf());
        $builder->expects($this->any())->method('setParentId')->with($this->equalTo($currentLevel - 1))
            ->will($this->returnSelf());
        $builder->expects($this->any())->method('setName')->with($this->equalTo('Name' . $currentLevel))
            ->will($this->returnSelf());
        $builder->expects($this->any())->method('setPosition')->with($this->equalTo($currentLevel))
            ->will($this->returnSelf());
        $builder->expects($this->any())->method('setLevel')->with($this->equalTo($currentLevel))
            ->will($this->returnSelf());
        $builder->expects($this->any())->method('setActive')->with($this->equalTo(true))
            ->will($this->returnSelf());
        $builder->expects($this->any())->method('setProductCount')->with(4)
            ->will($this->returnSelf());
        $builder->expects($this->any())->method('setChildren')->will($this->returnSelf());
        $builder->expects($this->any())->method('create')->will($this->returnValue([]));

        $this->treeBuilderFactory->expects($this->any())->method('create')->will($this->returnValue($builder));
        $node = $this->getMockBuilder('Magento\Framework\Data\Tree\Node')->disableOriginalConstructor()
            ->setMethods(
                [
                    'hasChildren',
                    'getChildren',
                    'getId',
                    'getParentId',
                    'getName',
                    'getPosition',
                    'getLevel',
                    'getIsActive',
                    'getProductCount'
                ]
            )
            ->getMock();
        $node->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $node->expects($this->any())->method('getChildren')->will($this->returnValue([$node]));

        $node->expects($this->any())->method('getId')->will($this->returnValue($currentLevel));
        $node->expects($this->any())->method('getParentId')->will($this->returnValue($currentLevel - 1));
        $node->expects($this->any())->method('getName')->will($this->returnValue('Name' . $currentLevel));
        $node->expects($this->any())->method('getPosition')->will($this->returnValue($currentLevel));
        $node->expects($this->any())->method('getLevel')->will($this->returnValue($currentLevel));
        $node->expects($this->any())->method('getIsActive')->will($this->returnValue(true));
        $node->expects($this->any())->method('getProductCount')->will($this->returnValue(4));
        $this->tree->getTree($node, $depth, $currentLevel);
    }
}
