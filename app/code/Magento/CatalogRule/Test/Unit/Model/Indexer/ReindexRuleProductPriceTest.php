<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ReindexRuleProductPriceTest extends \PHPUnit\Framework\TestCase
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
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var RuleProductPricesPersistor|MockObject
     */
    private $pricesPersistorMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDate;

    protected function setUp()
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->ruleProductsSelectBuilderMock = $this->createMock(RuleProductsSelectBuilder::class);
        $this->productPriceCalculatorMock = $this->createMock(ProductPriceCalculator::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->pricesPersistorMock = $this->createMock(RuleProductPricesPersistor::class);
        $this->localeDate = $this->createMock(TimezoneInterface::class);

        $this->model = new ReindexRuleProductPrice(
            $this->storeManagerMock,
            $this->ruleProductsSelectBuilderMock,
            $this->productPriceCalculatorMock,
            $this->dateTimeMock,
            $this->pricesPersistorMock,
            $this->localeDate
        );
    }

    public function testExecute()
    {
        $websiteId = 234;
        $storeGroupId = 30;
        $storeId = 40;
        $productMock = $this->createMock(Product::class);

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $websiteMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->willReturn($storeGroupId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);
        $storeGroupMock = $this->createMock(GroupInterface::class);
        $storeGroupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->with($storeGroupId)
            ->willReturn($storeGroupMock);

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $this->ruleProductsSelectBuilderMock->expects($this->once())
            ->method('build')
            ->with($websiteId, $productMock, true)
            ->willReturn($statementMock);

        $ruleData = [
            'product_id' => 100,
            'website_id' => 1,
            'customer_group_id' => 2,
            'from_time' => mktime(0, 0, 0, date('m'), date('d') - 100),
            'to_time' => mktime(0, 0, 0, date('m'), date('d') + 100),
            'action_stop' => true
        ];

        $this->dateTimeMock->expects($this->at(0))
            ->method('date')
            ->with('Y-m-d 00:00:00', $ruleData['from_time'])
            ->willReturn($ruleData['from_time']);
        $this->dateTimeMock->expects($this->at(1))
            ->method('timestamp')
            ->with($ruleData['from_time'])
            ->willReturn($ruleData['from_time']);
        $this->dateTimeMock->expects($this->at(2))
            ->method('date')
            ->with('Y-m-d 00:00:00', $ruleData['to_time'])
            ->willReturn($ruleData['to_time']);
        $this->dateTimeMock->expects($this->at(3))
            ->method('timestamp')
            ->with($ruleData['to_time'])
            ->willReturn($ruleData['to_time']);

        $statementMock->expects($this->at(0))
            ->method('fetch')
            ->willReturn($ruleData);
        $statementMock->expects($this->at(1))
            ->method('fetch')
            ->willReturn(false);

        $this->productPriceCalculatorMock->expects($this->atLeastOnce())
            ->method('calculate');
        $this->pricesPersistorMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->execute(1, $productMock, true));
    }
}
