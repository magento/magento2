<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Block\Info;

use Magento\Framework\View\Element\Template\Context;
use Magento\OfflinePayments\Block\Info\Checkmo;
use Magento\Payment\Model\Info;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * CheckmoTest contains list of test for block methods testing
 */
class CheckmoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Info|MockObject
     */
    private $info;

    /**
     * @var Checkmo
     */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->info = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalInformation'])
            ->getMock();

        $this->block = new Checkmo($context);
    }

    /**
     * @covers \Magento\OfflinePayments\Block\Info\Checkmo::getPayableTo
     * @param array $details
     * @param string|null $expected
     * @dataProvider getPayableToDataProvider
     */
    public function testGetPayableTo($details, $expected)
    {
        $this->info->expects(static::at(0))
            ->method('getAdditionalInformation')
            ->with('payable_to')
            ->willReturn($details);
        $this->block->setData('info', $this->info);

        static::assertEquals($expected, $this->block->getPayableTo());
    }

    /**
     * Get list of variations for payable configuration option testing
     * @return array
     */
    public function getPayableToDataProvider()
    {
        return [
            ['payable_to' => 'payable', 'payable'],
            ['', null]
        ];
    }

    /**
     * @covers \Magento\OfflinePayments\Block\Info\Checkmo::getMailingAddress
     * @param array $details
     * @param string|null $expected
     * @dataProvider getMailingAddressDataProvider
     */
    public function testGetMailingAddress($details, $expected)
    {
        $this->info->expects(static::at(1))
            ->method('getAdditionalInformation')
            ->with('mailing_address')
            ->willReturn($details);
        $this->block->setData('info', $this->info);

        static::assertEquals($expected, $this->block->getMailingAddress());
    }

    /**
     * Get list of variations for mailing address testing
     * @return array
     */
    public function getMailingAddressDataProvider()
    {
        return [
            ['mailing_address' => 'blah@blah.com', 'blah@blah.com'],
            ['mailing_address' => '', null]
        ];
    }

    /**
     * @covers \Magento\OfflinePayments\Block\Info\Checkmo::getMailingAddress
     */
    public function testConvertAdditionalDataIsNeverCalled()
    {
        $mailingAddress = 'blah@blah.com';
        $this->info->expects(static::at(1))
            ->method('getAdditionalInformation')
            ->with('mailing_address')
            ->willReturn($mailingAddress);
        $this->block->setData('info', $this->info);

        // First we set the property $this->_mailingAddress
        $this->block->getMailingAddress();

        // And now we get already setted property $this->_mailingAddress
        static::assertEquals($mailingAddress, $this->block->getMailingAddress());
    }
}
