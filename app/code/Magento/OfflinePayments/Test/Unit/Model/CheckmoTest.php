<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

class CheckmoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflinePayments\Model\Checkmo
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
            \Magento\OfflinePayments\Model\Checkmo::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );
    }

    public function testGetPayableTo()
    {
        $this->_object->setStore(1);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/checkmo/payable_to', 'store', 1)
            ->willReturn('payable');
        $this->assertEquals('payable', $this->_object->getPayableTo());
    }

    public function testGetMailingAddress()
    {
        $this->_object->setStore(1);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/checkmo/mailing_address', 'store', 1)
            ->willReturn('blah@blah.com');
        $this->assertEquals('blah@blah.com', $this->_object->getMailingAddress());
    }
}
