<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Observer;

class CatalogAttributeDeleteAfterObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Observer\CatalogAttributeDeleteAfterObserver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\SalesRule\Observer\CheckSalesRulesAvailability|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkSalesRulesAvailability;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            \Magento\SalesRule\Observer\CatalogAttributeDeleteAfterObserver::class,
            [
                'checkSalesRulesAvailability' => $this->checkSalesRulesAvailability
            ]
        );
    }

    protected function initMocks()
    {
        $this->checkSalesRulesAvailability = $this->createMock(
            \Magento\SalesRule\Observer\CheckSalesRulesAvailability::class
        );
    }

    public function testCatalogAttributeDeleteAfter()
    {
        $attributeCode = 'attributeCode';
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $event = $this->createPartialMock(\Magento\Framework\Event::class, ['getAttribute', '__wakeup']);
        $attribute = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['dataHasChangedFor', 'getIsUsedForPromoRules', 'getAttributeCode', '__wakeup']
        );

        $observer->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attribute);
        $attribute->expects($this->any())
            ->method('getIsUsedForPromoRules')
            ->willReturn(true);
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->checkSalesRulesAvailability
            ->expects($this->once())
            ->method('checkSalesRulesAvailability')
            ->willReturn('true');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }
}
