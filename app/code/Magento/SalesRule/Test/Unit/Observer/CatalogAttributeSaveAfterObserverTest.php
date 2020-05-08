<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Observer\CatalogAttributeSaveAfterObserver;
use Magento\SalesRule\Observer\CheckSalesRulesAvailability;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogAttributeSaveAfterObserverTest extends TestCase
{
    /**
     * @var CatalogAttributeSaveAfterObserver|MockObject
     */
    protected $model;

    /**
     * @var CheckSalesRulesAvailability|MockObject
     */
    protected $checkSalesRulesAvailability;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            CatalogAttributeSaveAfterObserver::class,
            [
                'checkSalesRulesAvailability' => $this->checkSalesRulesAvailability
            ]
        );
    }

    protected function initMocks()
    {
        $this->checkSalesRulesAvailability = $this->createMock(
            CheckSalesRulesAvailability::class
        );
    }

    public function testCatalogAttributeSaveAfter()
    {
        $attributeCode = 'attributeCode';
        $observer = $this->createMock(Observer::class);
        $event = $this->getMockBuilder(Event::class)
            ->addMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->createPartialMock(
            Attribute::class,
            ['dataHasChangedFor', 'getIsUsedForPromoRules', 'getAttributeCode']
        );

        $observer->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attribute);
        $attribute->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('is_used_for_promo_rules')
            ->willReturn(true);
        $attribute->expects($this->any())
            ->method('getIsUsedForPromoRules')
            ->willReturn(false);
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
