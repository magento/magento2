<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

class BanktransferTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflinePayments\Model\Banktransfer
     */
    protected $_object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_scopeConfig;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $paymentDataMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->_scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_object = $objectManagerHelper->getObject(
            \Magento\OfflinePayments\Model\Banktransfer::class,
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
