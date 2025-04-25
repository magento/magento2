<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\Plugin\IndexerConfigData as IndexerConfigDataPlugin;
use Magento\Catalog\Model\Indexer\Product\Flat\State as ProductFlatIndexerState;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Model\Config\Data as ConfigData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexerConfigDataTest extends TestCase
{
    /**
     * @var IndexerConfigDataPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ProductFlatIndexerState|MockObject
     */
    private $indexerStateMock;

    /**
     * @var ConfigData|MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->indexerStateMock = $this->getMockBuilder(ProductFlatIndexerState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(ConfigData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            IndexerConfigDataPlugin::class,
            ['state' => $this->indexerStateMock]
        );
    }

    /**
     * @param bool $isFlat
     * @param string $path
     * @param mixed $default
     * @param array $inputData
     * @param array $outputData
     *
     * @dataProvider afterGetDataProvider
     */
    public function testAfterGet($isFlat, $path, $default, $inputData, $outputData)
    {
        $this->indexerStateMock->expects(static::once())
            ->method('isFlatEnabled')
            ->willReturn($isFlat);

        $this->assertEquals($outputData, $this->plugin->afterGet($this->subjectMock, $inputData, $path, $default));
    }

    /**
     * @return array
     */
    public static function afterGetDataProvider()
    {
        $flatIndexerData = [
            'indexer_id' => 'catalog_product_flat',
            'action' => '\Action\Class',
            'title' => 'Title',
            'description' => 'Description'
        ];
        $otherIndexerData = [
            'indexer_id' => 'other_indexer',
            'action' => '\Action\Class',
            'title' => 'Title',
            'description' => 'Description'
        ];

        return [
            // flat is enabled, nothing is being changed
            [
                true,
                null,
                null,
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is absent, flat indexer is being removed
            [
                false,
                null,
                null,
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is null, flat indexer is being removed
            [
                false,
                null,
                null,
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is flat indexer, flat indexer is being removed
            [false, 'catalog_product_flat', null, $flatIndexerData, null],
            // flat is disabled, path is flat indexer, default is array(), flat indexer is being array()
            [false, 'catalog_product_flat', null, $flatIndexerData, null],
            // flat is disabled, path is other indexer, nothing is being changed
            [false, 'other_indexer', null, $otherIndexerData, $otherIndexerData]
        ];
    }
}
