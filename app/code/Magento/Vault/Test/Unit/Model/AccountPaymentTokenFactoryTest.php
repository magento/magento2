<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\AccountPaymentTokenFactory;
use Magento\Vault\Model\PaymentToken;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AccountPaymentTokenFactoryTest
 */
class AccountPaymentTokenFactoryTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->paymentToken = $objectManager->getObject(PaymentToken::class);

        $this->objectManager = $this->getMock(ObjectManagerInterface::class);
        $this->factory = new AccountPaymentTokenFactory($this->objectManager);
    }

    /**
     * @covers \Magento\Vault\Model\AccountPaymentTokenFactory::create
     */
    public function testCreate()
    {
        $this->objectManager->expects(static::once())
            ->method('create')
            ->willReturn($this->paymentToken);

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->factory->create();
        static::assertInstanceOf(PaymentTokenInterface::class, $paymentToken);
        static::assertEquals(AccountPaymentTokenFactory::TOKEN_TYPE_ACCOUNT, $paymentToken->getType());
    }
}
