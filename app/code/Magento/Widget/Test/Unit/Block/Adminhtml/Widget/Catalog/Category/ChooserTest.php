<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Catalog\Category;

class ChooserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Data\Tree\Node|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $childNode;

    /**
     * @var \Magento\Framework\Data\Tree\Node|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootNode;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryTree;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    public function setUp()
    {
        $this->collection = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Category\Collection',
            [],
            [],
            '',
            false
        );

        $this->childNode = $this->getMock(
            'Magento\Framework\Data\Tree\Node',
            ['getLevel', 'hasChildren'],
            [],
            '',
            false
        );
        $this->rootNode = $this->getMock(
            'Magento\Framework\Data\Tree\Node',
            ['getLevel', 'hasChildren', 'getChildren'],
            [],
            '',
            false
        );
        $this->categoryTree = $this->getMock('Magento\Catalog\Model\ResourceModel\Category\Tree', [], [], '', false);
        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->escaper = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->context = $this->getMock('Magento\Backend\Block\Template\Context', [], [], '', false);
    }

    public function testGetTreeHasLevelField()
    {
        $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        $storeGroups = [];
        $storeId = 1;
        $rootLevel = 2;
        $level = 3;

        $this->collection->expects($this->any())->method('addAttributeToSelect')->willReturnMap(
            [
                ['url_key', false, $this->collection],
                ['is_anchor', false, $this->collection]
            ]
        );

        $this->childNode->expects($this->atLeastOnce())->method('getLevel')->willReturn($level);

        $this->rootNode->expects($this->atLeastOnce())->method('getLevel')->willReturn($rootLevel);
        $this->rootNode->expects($this->once())->method('hasChildren')->willReturn(true);
        $this->rootNode->expects($this->once())->method('getChildren')->willReturn([$this->childNode]);

        $this->categoryTree->expects($this->once())->method('load')->with(null, 3)->willReturnSelf();
        $this->categoryTree->expects($this->atLeastOnce())
            ->method('addCollectionData')
            ->with($this->collection)
            ->willReturnSelf();
        $this->categoryTree->expects($this->once())->method('getNodeById')->with($rootId)->willReturn($this->rootNode);

        $this->store->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);

        $this->storeManager->expects($this->once())->method('getGroups')->willReturn($storeGroups);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($this->store);

        $this->context->expects($this->once())->method('getStoreManager')->willReturn($this->storeManager);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getEscaper')->willReturn($this->escaper);
        $this->context->expects($this->once())->method('getEventManager')->willReturn($this->eventManager);

        /** @var \Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser $chooser */
        $chooser = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                'Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser',
                [
                    'categoryTree' => $this->categoryTree,
                    'context' => $this->context
                ]
            );
        $chooser->setData('category_collection', $this->collection);
        $result = $chooser->getTree();
        $this->assertEquals($level, $result[0]['level']);
    }
}
