<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

class CashondeliveryTest extends \PHPUnit\Framework\TestCase
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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $paymentDataMock = $this->createMock(\Magento\Payment\Helper\Data::class);

        $this->_scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_object = $helper->getObject(
            \Magento\OfflinePayments\Model\Cashondelivery::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );
    }

    public function testGetInfoBlockType()
    {
        $this->assertEquals(\Magento\Payment\Block\Info\Instructions::class, $this->_object->getInfoBlockType());
    }
}
