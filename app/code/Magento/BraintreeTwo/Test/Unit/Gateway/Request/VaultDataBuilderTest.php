<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Request\PaymentDataBuilder;
use Magento\BraintreeTwo\Gateway\Request\VaultDataBuilder;
use Magento\Vault\Model\VaultPaymentInterface;

class VaultDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentDataBuilder
     */
    private $builder;

    /**
     * @var VaultPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultPaymentMock;

    public function setUp()
    {
        $this->vaultPaymentMock = $this->getMock(VaultPaymentInterface::class);

        $this->builder = new VaultDataBuilder($this->vaultPaymentMock);
    }

    public function testBuild()
    {
        $expectedResult = [
            VaultDataBuilder::OPTIONS => [VaultDataBuilder::STORE_IN_VAULT_ON_SUCCESS => true]
        ];

        $buildSubject = [];

        $this->vaultPaymentMock->expects(self::once())
            ->method('isActiveForPayment')
            ->willReturn(true);

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }

    public function testBuildWithSwitchedOffVault()
    {
        $expectedResult = [];

        $buildSubject = [];

        $this->vaultPaymentMock->expects(self::once())
            ->method('isActiveForPayment')
            ->willReturn(false);

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
