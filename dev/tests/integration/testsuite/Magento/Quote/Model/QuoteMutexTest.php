<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;

class QuoteMutexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    /**
     * @var QuoteMutexInterface
     */
    private $quoteMutex;

    private static $quoteMutexClass;

    protected function setUp(): void
    {
        $objectManager = BootstrapHelper::getObjectManager();
        $this->quoteMutex = $objectManager->create(QuoteMutexInterface::class);
        $this->guestCartManagement = $objectManager->create(GuestCartManagementInterface::class);
        self::$quoteMutexClass = $this;
    }

    /**
     * Tests quote mutex execution with different callables.
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
        $result = $this->quoteMutex->execute([$maskedQuoteId], $callable, $args);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array[]
     */
    public static function callableDataProvider(): array
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
                'callable' => \Closure::fromCallable([QuoteMutexTest::class, 'privateMethod']),
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
    private static function privateMethod(string $var)
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
        $this->quoteMutex->execute([], $callable);
    }
}
