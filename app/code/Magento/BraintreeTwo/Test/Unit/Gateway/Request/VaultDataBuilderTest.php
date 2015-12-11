<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Request\PaymentDataBuilder;
use Magento\BraintreeTwo\Gateway\Request\VaultDataBuilder;
use Magento\Vault\Gateway\Config\Config;

class VaultDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentDataBuilder
     */
    private $builder;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VaultDataBuilder($this->configMock);
    }

    public function testBuild()
    {
        $expectedResult = [
            VaultDataBuilder::OPTIONS => [VaultDataBuilder::STORE_IN_VAULT_ON_SUCCESS => true]
        ];

        $buildSubject = [];

        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with(Config::KEY_ACTIVE)
            ->willReturn(1);

        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with(Config::KEY_VAULT_PAYMENT)
            ->willReturn('braintreetwo');

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }

    public function testBuildWithSwitchedOffVault()
    {
        $expectedResult = [];

        $buildSubject = [];

        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with(Config::KEY_ACTIVE)
            ->willReturn(1);

        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with(Config::KEY_VAULT_PAYMENT)
            ->willReturn('anotherPaymentMethod');

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
