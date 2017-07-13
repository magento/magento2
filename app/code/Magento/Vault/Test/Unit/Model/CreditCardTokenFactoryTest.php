<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Vault\Model\PaymentToken;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CreditCardTokenFactoryTest
 */
class CreditCardTokenFactoryTest extends \PHPUnit\Framework\TestCase
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
     * @var CreditCardTokenFactory
     */
    private $factory;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $tokenTypes = [
            'account' => \Magento\Vault\Api\Data\PaymentTokenFactoryInterface::TOKEN_TYPE_ACCOUNT,
            'credit_card' => \Magento\Vault\Api\Data\PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD
        ];

        $this->paymentToken = $objectManager->getObject(PaymentToken::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->paymentTokenFactory = new PaymentTokenFactory($this->objectManager, $tokenTypes);
        $this->factory = new CreditCardTokenFactory($this->objectManager, $this->paymentTokenFactory);
    }

    /**
     * @covers \Magento\Vault\Model\CreditCardTokenFactory::create
     */
    public function testCreate()
    {
        $this->objectManager->expects(static::once())
            ->method('create')
            ->willReturn($this->paymentToken);

        $this->paymentToken->setType(\Magento\Vault\Api\Data\PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->factory->create();
        static::assertInstanceOf(PaymentTokenInterface::class, $paymentToken);
        static::assertEquals(CreditCardTokenFactory::TOKEN_TYPE_CREDIT_CARD, $paymentToken->getType());
    }
}
