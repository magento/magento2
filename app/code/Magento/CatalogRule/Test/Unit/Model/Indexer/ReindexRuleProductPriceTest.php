<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Generator;
use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductsPriceProcessor;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBatchProvider;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zend_Db_Statement_Interface;

class ReindexRuleProductPriceTest extends TestCase
{
    /**
     * @var ReindexRuleProductPrice
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var RuleProductsSelectBuilder|MockObject
     */
    private $ruleProductsSelectBuilderMock;

    /**
     * @var ProductPriceCalculator|MockObject
     */
    private $productPriceCalculatorMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDate;

    /**
     * @var RuleProductPricesPersistor|MockObject
     */
    private $pricesPersistorMock;

    /**
     * @var ReindexRuleProductsPriceProcessor|MockObject
     */
    private $reindexRuleProductsPriceProcessorMock;

    /**
     * @var RuleProductsSelectBatchProvider|MockObject
     */
    private $batchProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->ruleProductsSelectBuilderMock = $this->createMock(RuleProductsSelectBuilder::class);
        $this->productPriceCalculatorMock = $this->createMock(ProductPriceCalculator::class);
        $this->localeDate = $this->createMock(TimezoneInterface::class);
        $this->pricesPersistorMock = $this->createMock(RuleProductPricesPersistor::class);
        $this->reindexRuleProductsPriceProcessorMock = $this->createMock(ReindexRuleProductsPriceProcessor::class);
        $this->batchProvider = $this->createMock(RuleProductsSelectBatchProvider::class);

        $this->model = new ReindexRuleProductPrice(
            $this->storeManagerMock,
            $this->ruleProductsSelectBuilderMock,
            $this->productPriceCalculatorMock,
            $this->localeDate,
            $this->pricesPersistorMock,
            true,
            $this->reindexRuleProductsPriceProcessorMock,
            $this->batchProvider
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $websiteId = 234;
        $productId = 55;

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $statementMock = $this->createMock(Zend_Db_Statement_Interface::class);
        $this->ruleProductsSelectBuilderMock->expects($this->once())
            ->method('build')
            ->with($websiteId, $productId, true)
            ->willReturn($statementMock);

        $ruleData = [
            'product_id' => 100,
            'website_id' => 1,
            'customer_group_id' => 2,
            'from_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') - 100),
            'to_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') + 100),
            'action_stop' => true
        ];

        $statementMock
            ->method('fetch')
            ->willReturnOnConsecutiveCalls($ruleData, false);

        $this->reindexRuleProductsPriceProcessorMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->execute(1, $productId, true));
    }

    public function testFullIndexUsesOnlyBatchedStatements(): void
    {
        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn(7);
        $this->storeManagerMock->method('getWebsites')->willReturn([$website]);
        $statement = $this->createMock(Zend_Db_Statement_Interface::class);
        $statements = (static function () use ($statement): Generator {
            yield $statement;
        })();
        $this->batchProvider->expects($this->once())->method('execute')
            ->with(7, null, true)->willReturn($statements);
        $this->reindexRuleProductsPriceProcessorMock->expects($this->once())->method('executeBatches')
            ->with($statements, $website, 5000, true, true);
        // A fallback here would buffer the whole catalog again after processing every batch.
        $this->ruleProductsSelectBuilderMock->expects($this->never())->method('build');
        $this->reindexRuleProductsPriceProcessorMock->expects($this->never())->method('execute');

        self::assertTrue($this->model->execute(5000, null, true));
    }

    #[DataProvider('unbatchedPathProvider')]
    public function testDisabledOrPartialIndexUsesOriginalPath(
        bool $batching,
        ?int $productId,
        bool $working
    ): void {
        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn(7);
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$website]);
        $statement = $this->createMock(Zend_Db_Statement_Interface::class);
        $this->batchProvider->expects($this->never())->method('execute');
        $this->reindexRuleProductsPriceProcessorMock->expects($this->never())->method('executeBatches');
        $this->ruleProductsSelectBuilderMock->expects($this->once())->method('build')
            ->with(7, $productId, $working)->willReturn($statement);
        $this->reindexRuleProductsPriceProcessorMock->expects($this->once())->method('execute')
            ->with($statement, $website, 5000, $working, false);
        $model = new ReindexRuleProductPrice(
            $this->storeManagerMock,
            $this->ruleProductsSelectBuilderMock,
            $this->productPriceCalculatorMock,
            $this->localeDate,
            $this->pricesPersistorMock,
            false,
            $this->reindexRuleProductsPriceProcessorMock,
            $this->batchProvider,
            $batching
        );

        self::assertTrue($model->execute(5000, $productId, $working));
    }

    public static function unbatchedPathProvider(): array
    {
        return [
            'disabled full index' => [false, null, true],
            'enabled single product working table' => [true, 55, true],
            'enabled single product main table' => [true, 55, false],
            'enabled unrestricted main table' => [true, null, false]
        ];
    }
}
