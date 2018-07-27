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
class VaultDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

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
        $this->paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->paymentInfo = $this->getMock(InfoInterface::class);

        $this->builder = new VaultDataBuilder(new SubjectReader());
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
            'payment' => $this->paymentDO
        ];

        $this->paymentDO->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentInfo->method('getAdditionalInformation')
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
