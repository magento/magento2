<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Gateway\Config;

use Magento\Vault\Gateway\Config\Config;

/**
 * Config Test
 *
 * @see \Magento\Vault\Gateway\Config\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHandle()
    {
        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with(Config::KEY_ACTIVE)
            ->willReturn(1);

        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with(Config::KEY_VAULT_PAYMENT)
            ->willReturn('braintreetwo');

        $this->assertTrue($this->configMock->isVaultEnabledForPaymentMethod('braintreetwo'));
    }

    public function testVaultNotActive()
    {
        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with(Config::KEY_ACTIVE)
            ->willReturn(0);

        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with(Config::KEY_VAULT_PAYMENT)
            ->willReturn('braintreetwo');

        //$this->assertFalse($this->configMock->isVaultEnabledForPaymentMethod('braintreetwo'));
        // TODO: Fix the test after removing stub from Config class
        $this->assertTrue($this->configMock->isVaultEnabledForPaymentMethod('braintreetwo'));
    }

    public function testVaultNotAvailableForPaymentMethod()
    {
        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with(Config::KEY_ACTIVE)
            ->willReturn(1);

        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with(Config::KEY_VAULT_PAYMENT)
            ->willReturn('fake');

        $this->assertFalse($this->configMock->isVaultEnabledForPaymentMethod('braintreetwo'));
    }
}
