<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Observer;

class CatalogAttributeDeleteAfterObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Observer\CatalogAttributeDeleteAfterObserver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\SalesRule\Observer\CheckSalesRulesAvailability|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkSalesRulesAvailability;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            'Magento\SalesRule\Observer\CatalogAttributeDeleteAfterObserver',
            [
                'checkSalesRulesAvailability' => $this->checkSalesRulesAvailability
            ]
        );
    }

    protected function initMocks()
    {
        $this->checkSalesRulesAvailability = $this->getMock(
            'Magento\SalesRule\Observer\CheckSalesRulesAvailability',
            [],
            [],
            '',
            false
        );
    }

    public function testCatalogAttributeDeleteAfter()
    {
        $attributeCode = 'attributeCode';
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $event = $this->getMock('Magento\Framework\Event', ['getAttribute', '__wakeup'], [], '', false);
        $attribute = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            ['dataHasChangedFor', 'getIsUsedForPromoRules', 'getAttributeCode', '__wakeup'],
            [],
            '',
            false
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
