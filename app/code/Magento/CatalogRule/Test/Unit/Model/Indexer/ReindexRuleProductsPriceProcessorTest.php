<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductsPriceProcessor;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReindexRuleProductsPriceProcessorTest extends TestCase
{
    /**
     * @var ReindexRuleProductsPriceProcessor
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var RuleProductPricesPersistor|MockObject
     */
    private $pricesPersitorMock;

    /**
     * @var ProductPriceCalculator|MockObject
     */
    private $productPriceCalculatorMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->productPriceCalculatorMock = $this->createMock(ProductPriceCalculator::class);
        $this->pricesPersitorMock = $this->createMock(RuleProductPricesPersistor::class);
        $this->localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);

        $this->model = new ReindexRuleProductsPriceProcessor(
            $this->storeManagerMock,
            $this->productPriceCalculatorMock,
            $this->pricesPersitorMock,
            $this->localeDateMock
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $defaultGroupId = 11;
        $defaultStoreId = 22;

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->willReturn($defaultGroupId);
        $groupMock = $this->getMockForAbstractClass(GroupInterface::class);
        $groupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn($defaultStoreId);
        $this->storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->with($defaultGroupId)
            ->willReturn($groupMock);
        $this->productPriceCalculatorMock->expects($this->atLeastOnce())
            ->method('calculate');
        $this->pricesPersitorMock->expects($this->atLeastOnce())
            ->method('execute');
        $this->localeDateMock->expects($this->once())
            ->method('scopeDate')
            ->with($defaultStoreId, null, true)
            ->willReturn(new \DateTime());

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);

        $ruleData = [
            [
                'product_id' => 55,
                'website_id' => 234,
                'customer_group_id' => 2,
                'from_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') - 100),
                'to_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') + 100),
                'action_stop' => true
            ],
            [
                'product_id' => 66,
                'website_id' => 234,
                'customer_group_id' => 2,
                'from_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') - 100),
                'to_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') + 100),
                'action_stop' => true
            ]
        ];

        $statementMock
            ->method('fetch')
            ->willReturnOnConsecutiveCalls($ruleData[0], $ruleData[1], false);

        $this->model->execute(
            $statementMock,
            $websiteMock,
            100,
            true,
            true
        );
    }
}
