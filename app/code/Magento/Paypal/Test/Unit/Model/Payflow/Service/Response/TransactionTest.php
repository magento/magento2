<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see Transaction
 */
class TransactionTest extends TestCase
{
    /**
     * @covers \Magento\Paypal\Model\Payflow\Service\Response\Transaction::getResponseObject
     *
     * @param mixed $gatewayTransactionResponse
     */
    #[DataProvider('gatewayResponseInvariants')]
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
     */
    public function testSavePaymentInQuote()
    {
        $this->expectException('InvalidArgumentException');
        $cartId = 12;
        /** @var Transaction $transactionService */
        $transactionService = (new ObjectManager($this))->getObject(
            Transaction::class,
            [
                'quoteRepository' => $this->getCartRepositoryMock()
            ]
        );

        $transactionService->savePaymentInQuote(new DataObject(), $cartId);
    }

    /**
     * @return array
     */
    public static function gatewayResponseInvariants()
    {
        return [
            "Input data is a string" => ['testInput'],
            "Input data is an object" => [new \stdClass()],
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
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();
        return $objectManager->getObject(Transparent::class);
    }

    /**
     * @return Logger|MockObject
     */
    private function getLoggerMock()
    {
        return $this->createMock(Logger::class);
    }

    /**
     * @return CartRepositoryInterface|MockObject
     */
    private function getCartRepositoryMock()
    {
        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cart = $this->createMock(CartInterface::class);
        $cartRepository->method('get')->willReturn($cart);

        return $cartRepository;
    }
}
