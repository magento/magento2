<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

class PurchaseorderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Model\Purchaseorder
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);
        $paymentDataMock = $this->getMock(\Magento\Payment\Helper\Data::class, [], [], '', false);
        $this->_scopeConfig = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->_object = $objectManagerHelper->getObject(
            \Magento\OfflinePayments\Model\Purchaseorder::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );
    }

    public function testAssignData()
    {
        $data = new \Magento\Framework\DataObject([
            'po_number' => '12345'
        ]);

        $instance = $this->getMock(\Magento\Payment\Model\Info::class, [], [], '', false);
        $this->_object->setData('info_instance', $instance);
        $this->_object->assignData($data);
    }
}
