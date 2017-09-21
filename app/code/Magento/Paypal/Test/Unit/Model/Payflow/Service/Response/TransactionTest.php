<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @see Transaction
 */
class TransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Paypal\Model\Payflow\Service\Response\Transaction::getResponseObject
     *
     * @dataProvider gatewayResponseInvariants
     *
     * @param mixed $gatewayTransactionResponse
     */
    public function testGetResponseObject($gatewayTransactionResponse)
    {
        /** @var Transaction $transactionService */
        $transactionService = (new ObjectManager($this))->getObject(
            Transaction::class,
            [
                'transparent' => $this->getTransparentObject(),
                'logger' => $this->getLoggerMock()
            ]
        );

        $output = $transactionService->getResponseObject($gatewayTransactionResponse);

        $this->assertGetResponseObject($output);
    }

    /**
     * @covers \Magento\Paypal\Model\Payflow\Service\Response\Transaction::savePaymentInQuote
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSavePaymentInQuote()
    {
        /** @var Transaction $transactionService */
        $transactionService = (new ObjectManager($this))->getObject(
            Transaction::class,
            [
                'quoteRepository' => $this->getCartRepositoryMock()
            ]
        );

        $transactionService->savePaymentInQuote(new DataObject);
    }

    /**
     * @return array
     */
    public function gatewayResponseInvariants()
    {
        return [
            "Input data is a string" => ['testInput'],
            "Input data is an object" => [new \StdClass],
            "Input data is an array" => [['test' => 'input']]
        ];
    }

    /**
     * @param mixed $output
     */
    private function assertGetResponseObject($output)
    {
        $this->assertInstanceOf(
            DataObject::class,
            $output,
            "Method must return instance of \\Magento\\Framework\\DataObject."
        );
    }

    /**
     * @return Transparent|Object
     */
    private function getTransparentObject()
    {
        return (new ObjectManager($this))->getObject(Transparent::class);
    }

    /**
     * @return Logger|MockObject
     */
    private function getLoggerMock()
    {
        return $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return CartRepositoryInterface|MockObject
     */
    private function getCartRepositoryMock()
    {
        $cartRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->getMockForAbstractClass();

        $cart = $this->getMockBuilder(CartInterface::class)
            ->getMockForAbstractClass();

        $cartRepository->method('get')->willReturn($cart);

        return $cartRepository;
    }
}
