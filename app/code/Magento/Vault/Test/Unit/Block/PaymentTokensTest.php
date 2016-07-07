<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\PaymentTokens;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\PaymentToken;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PaymentTokensTest
 */
class PaymentTokensTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerTokenManagement|MockObject
     */
    private $tokenManagement;

    /**
     * @var PaymentTokens
     */
    private $block;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->tokenManagement = $this->getMockBuilder(CustomerTokenManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerSessionTokens'])
            ->getMock();

        $this->block = $this->objectManager->getObject(PaymentTokens::class, [
            'customerTokenManagement' => $this->tokenManagement
        ]);
    }

    /**
     * @covers \Magento\Vault\Block\PaymentTokens::getPaymentTokens
     */
    public function testGetPaymentTokens()
    {
        $cardToken = $this->objectManager->getObject(PaymentToken::class, [
            'data' => [PaymentTokenInterface::TYPE => PaymentTokenInterface::CARD_TYPE]
        ]);
        $token = $this->objectManager->getObject(PaymentToken::class, [
            'data' => [PaymentTokenInterface::TYPE => PaymentTokenInterface::TOKEN_TYPE]
        ]);
        $this->tokenManagement->expects(static::once())
            ->method('getCustomerSessionTokens')
            ->willReturn([$cardToken, $token]);

        $actual = $this->block->getPaymentTokens();
        static::assertCount(1, $actual);

        /** @var PaymentTokenInterface $actualToken */
        $actualToken = array_pop($actual);
        static::assertEquals(PaymentTokenInterface::TOKEN_TYPE, $actualToken->getType());
    }
}
