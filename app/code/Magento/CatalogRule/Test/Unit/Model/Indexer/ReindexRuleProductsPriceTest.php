<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\ReindexRuleProductsPrice;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductsPriceProcessor;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend_Db_Statement_Exception;

class ReindexRuleProductsPriceTest extends TestCase
{
    /**
     * @var ReindexRuleProductsPrice
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
        $this->reindexRuleProductsPriceProcessorMock = $this->createMock(ReindexRuleProductsPriceProcessor::class);

        $this->model = new ReindexRuleProductsPrice(
            $this->storeManagerMock,
            $this->reindexRuleProductsPriceProcessorMock,
            $this->ruleProductsSelectBuilderMock,
            true,
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function testExecute(): void
    {
        $websiteId = 234;
        $productIds = [55, 66];

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $this->ruleProductsSelectBuilderMock->expects($this->once())
            ->method('buildSelect')
            ->with($websiteId, $productIds, true)
            ->willReturn($statementMock);

        $ruleData = [
            [
                'product_id' => 100,
                'website_id' => 1,
                'customer_group_id' => 2,
                'from_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') - 100),
                'to_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') + 100),
                'action_stop' => true
            ],
            [
                'product_id' => 200,
                'website_id' => 1,
                'customer_group_id' => 2,
                'from_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') - 100),
                'to_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') + 100),
                'action_stop' => true
            ]
        ];

        $statementMock
            ->method('fetch')
            ->willReturnOnConsecutiveCalls($ruleData[0], $ruleData[1], false);

        $this->reindexRuleProductsPriceProcessorMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->execute(1, $productIds, true));
    }
}
