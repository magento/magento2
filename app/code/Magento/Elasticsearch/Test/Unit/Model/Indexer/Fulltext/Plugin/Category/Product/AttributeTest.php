<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Category\Product;

use ArrayIterator;
use Magento\Catalog\Model\ResourceModel\Attribute as AttributeResourceModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\Indexer\Fulltext\Plugin\Category\Product\Attribute;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for catalog search indexer plugin.
 */
class AttributeTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Processor|MockObject
     */
    private $indexerProcessorMock;

    /**
     * @var DimensionProviderInterface|MockObject
     */
    private $dimensionProviderMock;

    /**
     * @var IndexerHandlerFactory|MockObject
     */
    private $indexerHandlerFactoryMock;

    /**
     * @var Attribute
     */
    private $attributePlugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock = $this->createMock(Config::class);
        $this->indexerProcessorMock = $this->createMock(Processor::class);
        $this->dimensionProviderMock = $this->getMockBuilder(DimensionProviderInterface::class)
            ->getMockForAbstractClass();
        $this->indexerHandlerFactoryMock = $this->createMock(IndexerHandlerFactory::class);

        $this->attributePlugin = (new ObjectManager($this))->getObject(
            Attribute::class,
            [
                'config' => $this->configMock,
                'indexerProcessor' => $this->indexerProcessorMock,
                'dimensionProvider' => $this->dimensionProviderMock,
                'indexerHandlerFactory' => $this->indexerHandlerFactoryMock,
            ]
        );
    }

    /**
     * Test update catalog search indexer process.
     *
     * @param bool $isInvalid
     * @param bool $isElasticsearchEnabled
     * @param array $dimensions
     * @return void
     * @dataProvider afterSaveDataProvider
     *
     */
    public function testAfterSave(bool $isInvalid, bool $isElasticsearchEnabled, array $dimensions): void
    {
        $indexerData = ['indexer_example_data'];

        /** @var AttributeResourceModel|MockObject $subjectMock */
        $subjectMock = $this->createMock(AttributeResourceModel::class);
        /** @var IndexerInterface|MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->setMethods(['isInvalid', 'getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $indexerMock->expects($this->once())
            ->method('isInvalid')
            ->willReturn($isInvalid);

        $indexerMock->expects($this->getExpectsCount($isInvalid, $isElasticsearchEnabled))
            ->method('getData')
            ->willReturn($indexerData);

        $this->indexerProcessorMock->expects($this->once())
            ->method('getIndexer')
            ->willReturn($indexerMock);

        $this->configMock->expects($isInvalid ? $this->once() : $this->never())
            ->method('isElasticsearchEnabled')
            ->willReturn($isElasticsearchEnabled);

        /** @var IndexerHandler|MockObject $indexerHandlerMock */
        $indexerHandlerMock = $this->createMock(IndexerHandler::class);

        $indexerHandlerMock
            ->expects(($isInvalid && $isElasticsearchEnabled) ? $this->exactly(count($dimensions)) : $this->never())
            ->method('updateIndex')
            ->willReturnSelf();

        $this->indexerHandlerFactoryMock->expects($this->getExpectsCount($isInvalid, $isElasticsearchEnabled))
            ->method('create')
            ->with(['data' => $indexerData])
            ->willReturn($indexerHandlerMock);

        $this->dimensionProviderMock->expects($this->getExpectsCount($isInvalid, $isElasticsearchEnabled))
            ->method('getIterator')
            ->willReturn(new ArrayIterator($dimensions));

        $this->attributePlugin->afterSave($subjectMock, $subjectMock);
    }

    /**
     * DataProvider for testAfterSave().
     *
     * @return array
     */
    public function afterSaveDataProvider(): array
    {
        $dimensions = [['scope' => 1], ['scope' => 2]];

        return [
            'save_without_invalidation' => [false, false, []],
            'save_with_mysql_search' => [true, false, $dimensions],
            'save_with_elasticsearch' => [true, true, []],
            'save_with_elasticsearch_and_dimensions' => [true, true, $dimensions],
        ];
    }

    /**
     * Retrieves how many times method is executed.
     *
     * @param bool $isInvalid
     * @param bool $isElasticsearchEnabled
     * @return InvokedCountMatcher
     */
    private function getExpectsCount(bool $isInvalid, bool $isElasticsearchEnabled): InvokedCountMatcher
    {
        return ($isInvalid && $isElasticsearchEnabled) ? $this->once() : $this->never();
    }
}
