<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Block\Info;

use Magento\Framework\View\Element\Template\Context;
use Magento\OfflinePayments\Block\Info\Checkmo;
use Magento\Payment\Model\Info;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * CheckmoTest contains list of test for block methods testing
 */
class CheckmoTest extends TestCase
{
    /**
     * @var Info|MockObject
     */
    private $infoMock;

    /**
     * @var Checkmo
     */
    private $block;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();

        $this->infoMock = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAdditionalInformation'])
            ->getMock();

        $this->block = new Checkmo($context);
    }

    /**
     * @param array $details
     * @param string|null $expected
     *
     * @return void
     * @dataProvider getPayableToDataProvider
     * @covers \Magento\OfflinePayments\Block\Info\Checkmo::getPayableTo
     */
    public function testGetPayableTo($details, $expected): void
    {
        $this->infoMock
            ->method('getAdditionalInformation')
            ->willReturnCallback(function ($arg1) use ($details) {
                if ($arg1 == 'payable_to') {
                    return $details;
                }
            });
        $this->block->setData('info', $this->infoMock);

        static::assertEquals($expected, $this->block->getPayableTo());
    }

    /**
     * Get list of variations for payable configuration option testing.
     *
     * @return array
     */
    public static function getPayableToDataProvider(): array
    {
        return [
            ['details' => 'payable', 'payable'],
            ['', null]
        ];
    }

    /**
     * @param array $details
     * @param string|null $expected
     *
     * @return void
     * @dataProvider getMailingAddressDataProvider
     * @covers \Magento\OfflinePayments\Block\Info\Checkmo::getMailingAddress
     */
    public function testGetMailingAddress($details, $expected): void
    {
        $this->infoMock
            ->method('getAdditionalInformation')
            ->willReturnCallback(function ($arg1) use ($details) {
                if ($arg1 == 'mailing_address') {
                    return $details;
                } elseif (empty($arg1)) {
                    return null;
                }
            });

        $this->block->setData('info', $this->infoMock);

        static::assertEquals($expected, $this->block->getMailingAddress());
    }

    /**
     * Get list of variations for mailing address testing.
     *
     * @return array
     */
    public static function getMailingAddressDataProvider(): array
    {
        return [
            ['details' => 'blah@blah.com', 'blah@blah.com'],
            ['details' => '', null]
        ];
    }

    /**
     * @return void
     * @covers \Magento\OfflinePayments\Block\Info\Checkmo::getMailingAddress
     */
    public function testConvertAdditionalDataIsNeverCalled(): void
    {
        $mailingAddress = 'blah@blah.com';
        $this->infoMock
            ->method('getAdditionalInformation')
            ->willReturnCallback(function ($arg1) use ($mailingAddress) {
                if ($arg1 == 'mailing_address') {
                    return $mailingAddress;
                } elseif ($arg1 == []) {
                    return null;
                }
            });
        $this->block->setData('info', $this->infoMock);

        // First we set the property $this->_mailingAddress
        $this->block->getMailingAddress();

        // And now we get already setted property $this->_mailingAddress
        static::assertEquals($mailingAddress, $this->block->getMailingAddress());
    }
}
