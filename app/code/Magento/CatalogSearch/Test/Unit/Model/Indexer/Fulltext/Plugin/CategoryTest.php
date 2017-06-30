<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Category as CategoryPlugin;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CategoryResourceModel
     */
    protected $categoryResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CategoryModel
     */
    protected $categoryMock;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var CategoryPlugin
     */
    protected $model;

    protected function setUp()
    {
        $this->categoryMock = $this->getMockBuilder(CategoryModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryResourceMock = $this->getMockBuilder(CategoryResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryResourceMock->method('getConnection')->willReturn($connection);

        $this->indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getState', '__wakeup'])
            ->getMockForAbstractClass();
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->proceed = function () {
            return $this->categoryResourceMock;
        };

        $this->model = (new ObjectManager($this))->getObject(
            CategoryPlugin::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    public function testAfterSaveNonScheduled()
    {
        $this->categoryResourceMock->expects($this->once())->method('addCommitCallback');
        $this->assertEquals(
            $this->categoryResourceMock,
            $this->model->aroundSave($this->categoryResourceMock, $this->proceed, $this->categoryMock)
        );
    }

    public function testAfterSaveScheduled()
    {
        $this->categoryResourceMock->expects($this->once())->method('addCommitCallback');
        $this->assertEquals(
            $this->categoryResourceMock,
            $this->model->aroundSave($this->categoryResourceMock, $this->proceed, $this->categoryMock)
        );
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
