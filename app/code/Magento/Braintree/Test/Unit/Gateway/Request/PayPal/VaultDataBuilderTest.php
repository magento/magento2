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
<<<<<<< HEAD
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObjectMock;
=======
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;
>>>>>>> upstream/2.2-develop

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
<<<<<<< HEAD
        $this->paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
=======
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
>>>>>>> upstream/2.2-develop

        $this->paymentInfoMock = $this->createMock(InfoInterface::class);

<<<<<<< HEAD
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPayment'])
            ->getMock();

        $this->builder = new VaultDataBuilder($this->subjectReaderMock);
=======
        $this->builder = new VaultDataBuilder(new SubjectReader());
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
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
=======
            'payment' => $this->paymentDO
        ];

        $this->paymentDO->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentInfo->method('getAdditionalInformation')
>>>>>>> upstream/2.2-develop
            ->willReturn($additionalInfo);

        $actual = $this->builder->build($subject);
        self::assertEquals($expected, $actual);
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
