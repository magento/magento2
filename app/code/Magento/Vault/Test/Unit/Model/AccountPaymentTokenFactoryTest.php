<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\AccountPaymentTokenFactory;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccountPaymentTokenFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var PaymentToken
     */
    private $paymentToken;

    /**
     * @var AccountPaymentTokenFactory
     */
    private $factory;

    /**
     * @var PaymentTokenFactory
     */
    private $paymentTokenFactory;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $tokenTypes = [
            'account' => PaymentTokenFactoryInterface::TOKEN_TYPE_ACCOUNT,
            'credit_card' => PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD
        ];

        $this->paymentToken = $objectManager->getObject(PaymentToken::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->paymentTokenFactory = new PaymentTokenFactory($this->objectManager, $tokenTypes);
        $this->factory = new AccountPaymentTokenFactory($this->objectManager, $this->paymentTokenFactory);
    }

    /**
     * @covers \Magento\Vault\Model\AccountPaymentTokenFactory::create
     */
    public function testCreate()
    {
        $this->objectManager->expects(static::once())
            ->method('create')
            ->willReturn($this->paymentToken);

        $this->paymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_ACCOUNT);

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->factory->create();
        static::assertInstanceOf(PaymentTokenInterface::class, $paymentToken);
        static::assertEquals(AccountPaymentTokenFactory::TOKEN_TYPE_ACCOUNT, $paymentToken->getType());
    }
}
