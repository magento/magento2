<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Observer\CatalogAttributeDeleteAfterObserver;
use Magento\SalesRule\Observer\CheckSalesRulesAvailability;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogAttributeDeleteAfterObserverTest extends TestCase
{
    /**
     * @var CatalogAttributeDeleteAfterObserver|MockObject
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
            CatalogAttributeDeleteAfterObserver::class,
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

    public function testCatalogAttributeDeleteAfter()
    {
        $attributeCode = 'attributeCode';
        $observer = $this->createMock(Observer::class);
        $event = $this->createPartialMock(Event::class, ['getAttribute', '__wakeup']);
        $attribute = $this->createPartialMock(
            Attribute::class,
            ['dataHasChangedFor', 'getIsUsedForPromoRules', 'getAttributeCode', '__wakeup']
        );

        $observer->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($event));
        $event->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
        $attribute->expects($this->any())
            ->method('getIsUsedForPromoRules')
            ->will($this->returnValue(true));
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        $this->checkSalesRulesAvailability
            ->expects($this->once())
            ->method('checkSalesRulesAvailability')
            ->willReturn('true');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }
}
