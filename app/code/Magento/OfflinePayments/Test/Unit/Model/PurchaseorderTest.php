<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

class PurchaseorderTest extends \PHPUnit\Framework\TestCase
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
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $paymentDataMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->_scopeConfig = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
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

        $instance = $this->createMock(\Magento\Payment\Model\Info::class);
        $this->_object->setData('info_instance', $instance);
        $result = $this->_object->assignData($data);
        $this->assertEquals($result, $this->_object);
    }
}
