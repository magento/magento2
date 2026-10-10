<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Utility;

use Magento\Framework\App\Utility\AggregateInvoker;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\SkippedWithMessageException as SkippedTestError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AggregateInvokerTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var AggregateInvoker
     */
    protected $_invoker;

    /**
     * @var TestCase|MockObject
     */
    protected $_testCase;

    protected function setUp(): void
    {
        $this->_testCase = $this->createPartialMockWithReflection(
            Test::class,
            ['fail', 'markTestIncomplete', 'markTestSkipped', 'run', 'count']
        );
        $this->_invoker = new AggregateInvoker($this->_testCase, []);
    }

    /**
     *
     * @param string $expectedMessage
     * @param string $expectedMethod
     * @param string $exceptionClass
     * @throws
     */
    #[DataProvider('callbackDataProvider')]
    public function testMainFlow($expectedMessage, $expectedMethod, $exceptionClass)
    {
        $this->_testCase->expects(
            $this->any()
        )->method(
            $expectedMethod
        )->with(
            $this->stringStartsWith($expectedMessage)
        );
        $this->_invoker->__invoke(
            function () use ($exceptionClass) {
                throw new $exceptionClass('Some meaningful message.');
            },
            [[0]]
        );
    }

    /**
     * @return array
     */
    public static function callbackDataProvider()
    {
        return [
            [
                'Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.',
                'fail',
                AssertionFailedError::class,
            ],
            [
                'Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.',
                'fail',
                ExpectationFailedException::class
            ],
            [
                'Passed: 0, Failed: 0, Incomplete: 1, Skipped: 0.',
                'markTestIncomplete',
                IncompleteTestError::class
            ],
            [
                'Passed: 0, Failed: 0, Incomplete: 0, Skipped: 1.',
                'markTestSkipped',
                SkippedTestError::class
            ]
        ];
    }
}
