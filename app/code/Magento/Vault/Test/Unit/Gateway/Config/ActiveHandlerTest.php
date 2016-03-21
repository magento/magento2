<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Gateway\Config;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Vault\Gateway\Config\ActiveHandler;
use Magento\Vault\Model\Adminhtml\Source\VaultProvidersMap;

/**
 * Class ActiveHandlerTest
 *
 * @see \Magento\Vault\Gateway\Config\ActiveHandler
 */
class ActiveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ActiveHandler
     */
    private $activeHandler;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->activeHandler = new ActiveHandler($this->configMock);
    }

    /**
     * Run test for handle method (true)
     */
    public function testHandle()
    {
        $this->configMock->expects(self::at(0))
            ->method('getValue')
            ->with(VaultProvidersMap::VALUE_CODE)
            ->willReturn('code');
        $this->configMock->expects(self::at(1))
            ->method('getValue')
            ->with('active')
            ->willReturn(1);

        self::assertEquals(1, $this->activeHandler->handle([], 1));
    }

    /**
     * Run test for handle method
     */
    public function testHandleVaultNotActive()
    {
        $this->configMock->expects(self::at(0))
            ->method('getValue')
            ->with(VaultProvidersMap::VALUE_CODE)
            ->willReturn('code');
        $this->configMock->expects(self::at(1))
            ->method('getValue')
            ->with('active')
            ->willReturn(0);

        self::assertEquals(0, $this->activeHandler->handle([], 1));
    }

    /**
     * Run test for handle method
     */
    public function testHandleVaultPaymentNotSet()
    {
        $this->configMock->expects(self::at(0))
            ->method('getValue')
            ->with(VaultProvidersMap::VALUE_CODE)
            ->willReturn(VaultProvidersMap::EMPTY_VALUE);
        $this->configMock->expects(self::at(1))
            ->method('getValue')
            ->with('active')
            ->willReturn(1);

        self::assertEquals(0, $this->activeHandler->handle([], 1));
    }
}
