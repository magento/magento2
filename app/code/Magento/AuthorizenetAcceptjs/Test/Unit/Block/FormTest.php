<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Block;

use Magento\AuthorizenetAcceptjs\Block\Form;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config as PaymentConfig;

class FormTest extends TestCase
{
    /**
     * @var Form
     */
    private $block;

    /**
     * @var Config|MockObject|InvocationMocker
     */
    private $configMock;

    protected function setUp()
    {
        $contextMock = $this->createMock(Context::class);
        $this->configMock = $this->createMock(Config::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $quoteMock->method('getStoreId')
            ->willReturn('123');
        $paymentConfig = $this->createMock(PaymentConfig::class);

        $this->block = new Form(
            $contextMock,
            $paymentConfig,
            $this->configMock,
            $quoteMock
        );
    }

    public function testIsCvvEnabled()
    {
        $this->configMock->method('isCvvEnabled')
            ->with('123')
            ->willReturn(true);
        $this->assertTrue($this->block->isCvvEnabled());
    }
}
