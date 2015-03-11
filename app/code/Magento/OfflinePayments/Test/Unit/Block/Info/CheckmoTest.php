<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Block\Info;

class CheckmoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Block\Info\Checkmo
     */
    protected $_model;

    protected function setUp()
    {
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->_model = new \Magento\OfflinePayments\Block\Info\Checkmo($context);
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
        $this->_model->setData('info', $info);

        $this->assertEquals($expected, $this->_model->getPayableTo());
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
        $this->_model->setData('info', $info);

        $this->assertEquals($expected, $this->_model->getMailingAddress());
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

    public function testConvertAdditionalDataIsNeverCalled()
    {
        $info = $this->getMock('Magento\Payment\Model\Info', ['getAdditionalData'], [], '', false);
        $info->expects($this->once())
            ->method('getAdditionalData')
            ->willReturn(serialize(['mailing_address' => 'blah@blah.com']));
        $this->_model->setData('info', $info);

        // First we set the property $this->_mailingAddress
        $this->_model->getMailingAddress();

        // And now we get already setted property $this->_mailingAddress
        $this->assertEquals('blah@blah.com', $this->_model->getMailingAddress());
    }
}
