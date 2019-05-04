<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\PayPal\VaultDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\PayPal\VaultDataBuilder.
 */
class VaultDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentInfoMock;

    /**
     * @var VaultDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);

        $this->paymentInfoMock = $this->createMock(InfoInterface::class);

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPayment'])
            ->getMock();

        $this->builder = new VaultDataBuilder($this->subjectReaderMock);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Request\PayPal\VaultDataBuilder::build
     * @param array $additionalInfo
     * @param array $expected
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $additionalInfo, array $expected)
    {
        $subject = [
            'payment' => $this->paymentDataObjectMock,
        ];

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($this->paymentDataObjectMock);

        $this->paymentDataObjectMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentInfoMock);

        $this->paymentInfoMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInfo);

        $actual = $this->builder->build($subject);
        static::assertEquals($expected, $actual);
    }

    /**
     * Get variations to test build method
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            [
                'additionalInfo' => [
                    VaultConfigProvider::IS_ACTIVE_CODE => true
                ],
                'expected' => [
                    'options' => [
                        'storeInVaultOnSuccess' => true
                    ]
                ]
            ],
            [
                'additionalInfo' => [
                    VaultConfigProvider::IS_ACTIVE_CODE => false
                ],
                'expected' => []
            ],
            [
                'additionalInfo' => [],
                'expected' => []
            ],
        ];
    }
}
