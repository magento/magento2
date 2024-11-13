<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Catalog\Category;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChooserTest extends TestCase
{
    /**
     * @var Collection|MockObject
     */
    protected $collection;

    /**
     * @var Node|MockObject
     */
    protected $childNode;

    /**
     * @var Node|MockObject
     */
    protected $rootNode;

    /**
     * @var Tree|MockObject
     */
    protected $categoryTree;

    /**
     * @var Store|MockObject
     */
    protected $store;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var Context|MockObject
     */
    protected $context;

    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);

        $this->childNode = $this->getMockBuilder(Node::class)
            ->addMethods(['getLevel'])
            ->onlyMethods(['hasChildren', 'getIdField'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rootNode = $this->getMockBuilder(Node::class)
            ->addMethods(['getLevel'])
            ->onlyMethods(['hasChildren', 'getChildren', 'getIdField'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryTree = $this->createMock(Tree::class);
        $this->store = $this->createMock(Store::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->context = $this->createMock(Context::class);
    }

    public function testGetTreeHasLevelField()
    {
        $rootId = Category::TREE_ROOT_ID;
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

        $this->rootNode->method('getIdField')->willReturn('test_id_field');
        $this->childNode->method('getIdField')->willReturn('test_id_field');
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

        /** @var Chooser $chooser */
        $chooser = (new ObjectManager($this))
            ->getObject(
                Chooser::class,
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
