<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Request\PayPal\VaultDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class VaultDataBuilderTest
 */
class VaultDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObject;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentInfo;

    /**
     * @var VaultDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->paymentDataObject = $this->createMock(PaymentDataObjectInterface::class);

        $this->paymentInfo = $this->createMock(InfoInterface::class);

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPayment'])
            ->getMock();

        $this->builder = new VaultDataBuilder($this->subjectReader);
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
            'payment' => $this->paymentDataObject
        ];

        $this->subjectReader->expects(static::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($this->paymentDataObject);

        $this->paymentDataObject->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentInfo->expects(static::once())
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
