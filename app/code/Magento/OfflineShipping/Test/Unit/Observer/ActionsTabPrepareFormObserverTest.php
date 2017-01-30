<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Observer;

use Magento\OfflineShipping\Model\SalesRule\Rule;

class ActionsTabPrepareFormObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Observer\SalesRule\ActionsTabPrepareFormObserver
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\OfflineShipping\Observer\SalesRule\ActionsTabPrepareFormObserver();
    }

    public function testPrepareForm()
    {
        $observerMock = $this->getMockBuilder('\Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getForm'])
            ->getMock();

        $formMock = $this->getMockBuilder('\Magento\Framework\Data\Form')
            ->disableOriginalConstructor()
            ->setMethods(['getElements'])
            ->getMock();

        $elementMock = $this->getMockBuilder('\Magento\Framework\Data\Form\Element\AbstractElement')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'addField'])
            ->getMock();

        $elementMock->expects($this->once())
            ->method('getId')
            ->willReturn('action_fieldset');

        $elementMock->expects($this->once())
            ->method('addField')
            ->with(
                'simple_free_shipping',
                'select',
                [
                    'label' => __('Free Shipping'),
                    'title' => __('Free Shipping'),
                    'name' => 'simple_free_shipping',
                    'options' => [
                        0 => __('No'),
                        Rule::FREE_SHIPPING_ITEM => __('For matching items only'),
                        Rule::FREE_SHIPPING_ADDRESS => __('For shipment with matching items'),
                    ]
                ]
            );

        $formMock->expects($this->once())
            ->method('getElements')
            ->willReturn([$elementMock]);

        $observerMock->expects($this->once())
            ->method('getForm')
            ->willReturn($formMock);

        $this->model->execute($observerMock);
    }
}
