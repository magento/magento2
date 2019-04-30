<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Block;

use Magento\AuthorizenetAcceptjs\Block\Form;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Test for Magento\AuthorizenetAcceptjs\Block\Form
 */
class FormTest extends TestCase
{
    /**
     * @var Form
     */
    private $block;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $contextMock = $this->createMock(Context::class);
        $this->configMock = $this->createMock(Config::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $quoteMock->method('getStoreId')
            ->willReturn('123');
        $paymentConfig = $this->createMock(PaymentConfig::class);
        $this->block = $this->objectManagerHelper->getObject(
            Form::class,
            [
                'templateContext' => $contextMock,
                '_paymentConfig' => $paymentConfig,
                'config' => $this->configMock,
                'sessionQuote' => $quoteMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testIsCvvEnabled()
    {
        $this->configMock->method('isCvvEnabled')
            ->with('123')
            ->willReturn(true);
        $this->assertTrue($this->block->isCvvEnabled());
    }
}
