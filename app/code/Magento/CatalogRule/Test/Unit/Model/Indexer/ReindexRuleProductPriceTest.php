<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductsPriceProcessor;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->ruleProductsSelectBuilderMock = $this->createMock(RuleProductsSelectBuilder::class);
        $this->productPriceCalculatorMock = $this->createMock(ProductPriceCalculator::class);
        $this->localeDate = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->pricesPersistorMock = $this->createMock(RuleProductPricesPersistor::class);
        $this->reindexRuleProductsPriceProcessorMock = $this->createMock(ReindexRuleProductsPriceProcessor::class);

        $this->model = new ReindexRuleProductPrice(
            $this->storeManagerMock,
            $this->ruleProductsSelectBuilderMock,
            $this->productPriceCalculatorMock,
            $this->localeDate,
            $this->pricesPersistorMock,
            true,
            $this->reindexRuleProductsPriceProcessorMock
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $websiteId = 234;
        $productId = 55;

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
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
}
