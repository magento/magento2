<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Model;

class CashondeliveryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Model\Cashondelivery
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);

        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $this->_object = $helper->getObject(
            'Magento\OfflinePayments\Model\Cashondelivery',
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );
    }

    public function testGetInfoBlockType()
    {
        $this->assertEquals('Magento\Payment\Block\Info\Instructions', $this->_object->getInfoBlockType());
    }

    public function testGetInstructions()
    {
        $this->_object->setStore(1);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/cashondelivery/instructions', 'store', 1)
            ->willReturn('payment configuration');
        $this->assertEquals('payment configuration', $this->_object->getInstructions());
    }
}
