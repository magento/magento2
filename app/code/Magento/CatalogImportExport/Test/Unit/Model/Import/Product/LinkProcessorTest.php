<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\LinkProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkProcessor;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected $product;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\LinkFactory
     */
    protected $linkFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor::class
     */
    protected $skuProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->resourceHelper = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Helper::class);

        $this->resource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Link::class);
        $this->resource->method('getMainTable')->willReturn('main_link_table');

        $this->linkFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\LinkFactory::class,
            ['create']
        );
        $this->linkFactory->method('create')->willReturn($this->resource);

        $this->skuProcessor = $this->createMock(
            \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor::class
        );
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
    }

    /**
     * @dataProvider diConfigDataProvider
     * @param $expectedCallCount
     * @param $linkToNameId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testSaveLinks($expectedCallCount, $linkToNameId)
    {
        $this->linkProcessor =
            new \Magento\CatalogImportExport\Model\Import\Product\LinkProcessor(
                $this->linkFactory,
                $this->resourceHelper,
                $this->skuProcessor,
                $this->logger,
                $linkToNameId
            );

        $importEntity = $this->createMock(\Magento\CatalogImportExport\Model\Import\Product::class);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $importEntity->method('getConnection')->willReturn($connection);
        $select = $this->createMock(\Magento\Framework\DB\Select::class);

        // expect one call per linkToNameId
        $connection->expects($this->exactly($expectedCallCount))->method('select')->willReturn($select);

        $select->method('from')->willReturn($select);

        $dataSourceModel = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);

        $this->linkProcessor->saveLinks($importEntity, $dataSourceModel, '_related_');
    }

    /**
     * @return array
     */
    public function diConfigDataProvider()
    {
        return [
            [3, [
                '_related_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
                '_crosssell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
                '_upsell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
            ]],
            [4, [
                '_related_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
                '_crosssell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
                '_upsell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
                '_custom_link_' => 9,
            ]],
        ];
    }
}
