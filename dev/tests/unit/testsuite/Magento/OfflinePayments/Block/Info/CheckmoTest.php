<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Info;

class CheckmoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Block\Info\Checkmo
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $this->_scopeConfig = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->_object = $objectManagerHelper->getObject(
            'Magento\OfflinePayments\Block\Info\Checkmo',
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );
    }

    /**
     * @dataProvider getPayableToDataProvider
     */
    public function testGetPayableTo($details, $expected)
    {
        $info = $this->getMock('Magento\Payment\Model\Info', ['getAdditionalData'], [], '', false);
        $info->expects($this->once())
            ->method('getAdditionalData')
            ->willReturn(serialize($details));
        $this->_object->setData('info', $info);

        $this->assertEquals($expected, $this->_object->getPayableTo());
    }

    /**
     * @return array
     */
    public function getPayableToDataProvider()
    {
        return [
            [['payable_to' => 'payable'], 'payable'],
            ['', '']
        ];
    }

    /**
     * @dataProvider getMailingAddressDataProvider
     */
    public function testGetMailingAddress($details, $expected)
    {
        $info = $this->getMock('Magento\Payment\Model\Info', ['getAdditionalData'], [], '', false);
        $info->expects($this->once())
            ->method('getAdditionalData')
            ->willReturn(serialize($details));
        $this->_object->setData('info', $info);

        $this->assertEquals($expected, $this->_object->getMailingAddress());
    }

    /**
     * @return array
     */
    public function getMailingAddressDataProvider()
    {
        return [
            [['mailing_address' => 'blah@blah.com'], 'blah@blah.com'],
            ['', '']
        ];
    }
}
