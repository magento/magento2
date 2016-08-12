<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Product;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product\Action as ProductActionIndexerPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductActionIndexerPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerMock;

    /**
     * @var ProductAction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    protected function setUp()
    {
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(ProductAction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerRegistryMock->expects(static::once())
            ->method('get')
            ->with(FulltextIndexer::INDEXER_ID)
            ->willReturn($this->indexerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            ProductActionIndexerPlugin::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    public function testAfterUpdateAttributesNonScheduled()
    {
        $productIds = [1, 2, 3];

        $this->indexerMock->expects(static::once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexerMock->expects(static::once())
            ->method('reindexList')
            ->with($productIds);

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterUpdateAttributes($this->subjectMock, $this->subjectMock, $productIds, [], null)
        );
    }

    public function testAfterUpdateAttributesScheduled()
    {
        $productIds = [1, 2, 3];

        $this->indexerMock->expects(static::once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexerMock->expects(static::never())
            ->method('reindexList');

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterUpdateAttributes($this->subjectMock, $this->subjectMock, $productIds, [], null)
        );
    }

    public function testAfterUpdateWebsitesNonScheduled()
    {
        $productIds = [1, 2, 3];

        $this->indexerMock->expects(static::once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexerMock->expects(static::once())
            ->method('reindexList')
            ->with($productIds);

        $this->plugin->afterUpdateWebsites($this->subjectMock, $this->subjectMock, $productIds, [], null);
    }

    public function testAfterUpdateWebsitesScheduled()
    {
        $productIds = [1, 2, 3];

        $this->indexerMock->expects(static::once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexerMock->expects(static::never())
            ->method('reindexList');

        $this->plugin->afterUpdateWebsites($this->subjectMock, $this->subjectMock, $productIds, [], null);
    }
}
