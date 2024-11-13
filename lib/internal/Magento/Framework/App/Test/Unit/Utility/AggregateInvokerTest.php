<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\TestCase;

class AggregateInvokerTest extends TestCase
{
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
        $this->_testCase = $this->getMockBuilder(Test::class)
            ->addMethods(['fail', 'markTestIncomplete', 'markTestSkipped'])
            ->onlyMethods(['run', 'count'])
            ->getMock();
        $this->_invoker = new AggregateInvoker($this->_testCase, []);
    }

    /**
     * @dataProvider callbackDataProvider
     *
     * @param string $expectedMessage
     * @param string $expectedMethod
     * @param string $exceptionClass
     * @throws
     */
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
