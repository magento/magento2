<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\IndexBuilder;

class ReindexRuleProductPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice
     */
    private $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleProductsSelectBuilderMock;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productPriceCalculatorMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pricesPersistorMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleProductsSelectBuilderMock =
            $this->getMockBuilder(\Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productPriceCalculatorMock =
            $this->getMockBuilder(\Magento\CatalogRule\Model\Indexer\ProductPriceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pricesPersistorMock =
            $this->getMockBuilder(\Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice(
            $this->storeManagerMock,
            $this->ruleProductsSelectBuilderMock,
            $this->productPriceCalculatorMock,
            $this->dateTimeMock,
            $this->pricesPersistorMock
        );
    }

    public function testExecute()
    {
        $websiteId = 234;
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->once())->method('getId')->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$websiteMock]);

        $statementMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $statementMock->expects($this->at(0))->method('fetch')->willReturn($ruleData);
        $statementMock->expects($this->at(1))->method('fetch')->willReturn(false);

        $this->productPriceCalculatorMock->expects($this->atLeastOnce())->method('calculate');
        $this->pricesPersistorMock->expects($this->once())->method('execute');

        $this->assertTrue($this->model->execute(1, $productMock, true));
    }
}
