<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;

class PlaceOrderMutexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    /**
     * @var PlaceOrderMutexInterface
     */
    private $placeOrderMutex;

    protected function setUp(): void
    {
        $objectManager = BootstrapHelper::getObjectManager();
        $this->placeOrderMutex = $objectManager->create(PlaceOrderMutexInterface::class);
        $this->guestCartManagement = $objectManager->create(GuestCartManagementInterface::class);
    }

    /**
     * Tests place order execution with different callables.
     *
     * @param callable $callable
     * @param array $args
     * @param mixed $expectedResult
     * @return void
     * @dataProvider callableDataProvider
     */
    public function testSuccessfulExecution(callable $callable, array $args, $expectedResult): void
    {
        $maskedQuoteId = $this->guestCartManagement->createEmptyCart();
        $result = $this->placeOrderMutex->execute($maskedQuoteId, $callable, $args);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array[]
     */
    public function callableDataProvider(): array
    {
        $functionWithArgs = function (int $a, int $b) {
            return $a + $b;
        };

        $functionWithoutArgs = function () {
            return 'Function without args';
        };

        return [
            ['callable' => $functionWithoutArgs, 'args' => [], 'expectedResult' => 'Function without args'],
            ['callable' => $functionWithArgs, 'args' => [1,2], 'expectedResult' => 3],
            [
                'callable' => \Closure::fromCallable([$this, 'privateMethod']),
                'args' => ['test'],
                'expectedResult' => 'test'
            ],
        ];
    }

    /**
     * Private method for data provider.
     *
     * @param string $var
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function privateMethod(string $var): string
    {
        return $var;
    }

    /**
     * Tests exception when empty maskIds array has been provided.
     *
     * @return void
     */
    public function testWithEmptyMaskIdsArgument(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $callable = function () {
        };
        $this->placeOrderMutex->execute('', $callable);
    }
}
