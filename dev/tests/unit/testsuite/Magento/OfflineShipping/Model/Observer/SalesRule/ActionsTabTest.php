<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Observer\SalesRule;

class ActionsTabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Observer\SalesRule\ActionsTab
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new ActionsTab();
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
            ->method('addField');

        $formMock->expects($this->once())
            ->method('getElements')
            ->willReturn([$elementMock]);

        $observerMock->expects($this->once())
            ->method('getForm')
            ->willReturn($formMock);

        $this->model->prepareForm($observerMock);
    }
}
