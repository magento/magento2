<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\LinkProcessor;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkProcessorTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var LinkProcessor|MockObject
     */
    protected $linkProcessor;

    /**
     * @var AbstractType
     */
    protected $product;

    /**
     * @var Helper
     */
    protected $resourceHelper;

    /**
     * @var Link
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\LinkFactory
     */
    protected $linkFactory;

    /**
     * @var SkuProcessor ::class
     */
    protected $skuProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->resourceHelper = $this->createMock(Helper::class);

        $this->resource = $this->createMock(Link::class);
        $this->resource->method('getMainTable')->willReturn('main_link_table');

        $this->linkFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\LinkFactory::class,
            ['create']
        );
        $this->linkFactory->method('create')->willReturn($this->resource);

        $this->skuProcessor = $this->createMock(
            SkuProcessor::class
        );
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
    }

    /**
     * @dataProvider diConfigDataProvider
     * @param $expectedCallCount
     * @param $linkToNameId
     * @throws LocalizedException
     */
    public function testSaveLinks($expectedCallCount, $linkToNameId)
    {
        $this->linkProcessor =
            new LinkProcessor(
                $this->linkFactory,
                $this->resourceHelper,
                $this->skuProcessor,
                $this->logger,
                $linkToNameId
            );

        $importEntity = $this->createMock(Product::class);
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $importEntity->method('getConnection')->willReturn($connection);
        $select = $this->createMock(Select::class);

        // expect one call per linkToNameId
        $connection->expects($this->exactly($expectedCallCount))->method('select')->willReturn($select);

        $select->method('from')->willReturn($select);

        $dataSourceModel = $this->createMock(Data::class);

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
