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
     * @var TimezoneInterface|MockObject
     */
    private $localeDate;

    /**
     * @var RuleProductPricesPersistor|MockObject
     */
    private $pricesPersistorMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->ruleProductsSelectBuilderMock = $this->createMock(RuleProductsSelectBuilder::class);
        $this->productPriceCalculatorMock = $this->createMock(ProductPriceCalculator::class);
        $this->localeDate = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->pricesPersistorMock = $this->createMock(RuleProductPricesPersistor::class);

        $this->model = new ReindexRuleProductPrice(
            $this->storeManagerMock,
            $this->ruleProductsSelectBuilderMock,
            $this->productPriceCalculatorMock,
            $this->localeDate,
            $this->pricesPersistorMock
        );
    }

    public function testExecute()
    {
        $websiteId = 234;
        $defaultGroupId = 11;
        $defaultStoreId = 22;
        $productId = 55;

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $websiteMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->willReturn($defaultGroupId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);
        $groupMock = $this->getMockForAbstractClass(GroupInterface::class);
        $groupMock->method('getId')
            ->willReturn($defaultStoreId);
        $groupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn($defaultStoreId);
        $this->storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->with($defaultGroupId)
            ->willReturn($groupMock);

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $this->ruleProductsSelectBuilderMock->expects($this->once())
            ->method('build')
            ->with($websiteId, $productId, true)
            ->willReturn($statementMock);

        $ruleData = [
            'product_id' => 100,
            'website_id' => 1,
            'customer_group_id' => 2,
            'from_time' => mktime(0, 0, 0, date('m'), date('d') - 100),
            'to_time' => mktime(0, 0, 0, date('m'), date('d') + 100),
            'action_stop' => true
        ];

        $this->localeDate->expects($this->once())
            ->method('scopeDate')
            ->with($defaultStoreId, null, true)
            ->willReturn(new \DateTime());

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

        $this->assertTrue($this->model->execute(1, $productId, true));
    }
}
