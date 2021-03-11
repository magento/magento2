<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway;

use Magento\Framework\Config\Data;
use Magento\Payment\Model\Method\Adapter;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testVerifyConfiguration()
    {
        /** @var Adapter $paymentAdapter */
        $paymentAdapter = $this->objectManager->get('AuthorizenetAcceptjsFacade');

        $this->assertEquals('authorizenet_acceptjs', $paymentAdapter->getCode());
        $this->assertTrue($paymentAdapter->canAuthorize());
        $this->assertTrue($paymentAdapter->canCapture());
        $this->assertFalse($paymentAdapter->canCapturePartial());
        $this->assertTrue($paymentAdapter->canRefund());
        $this->assertTrue($paymentAdapter->canUseCheckout());
        $this->assertTrue($paymentAdapter->canVoid());
        $this->assertTrue($paymentAdapter->canUseInternal());
        $this->assertTrue($paymentAdapter->canEdit());
        $this->assertTrue($paymentAdapter->canFetchTransactionInfo());

        /** @var Data $configReader */
        $configReader = $this->objectManager->get('Magento\Payment\Model\Config\Data');
        $value = $configReader->get('methods/authorizenet_acceptjs/allow_multiple_address');

        $this->assertSame('0', $value);
    }
}
