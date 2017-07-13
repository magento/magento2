<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Utility;

use \Magento\Framework\App\Utility\AggregateInvoker;

class AggregateInvokerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Utility\AggregateInvoker
     */
    protected $_invoker;

    /**
     * @var \PHPUnit\Framework\TestCase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_testCase;

    protected function setUp()
    {
        $this->_testCase = $this->createPartialMock(
            \PHPUnit\Framework\Test::class,
            ['run', 'count', 'fail', 'markTestIncomplete', 'markTestSkipped']
        );
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
    public function callbackDataProvider()
    {
        return [
            [
                'Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.',
                'fail',
                \PHPUnit\Framework\AssertionFailedError::class,
            ],
            ['Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.', 'fail', \PHPUnit\Framework\OutputError::class],
            [
                'Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.',
                'fail',
                \PHPUnit\Framework\ExpectationFailedException::class
            ],
            [
                'Passed: 0, Failed: 0, Incomplete: 1, Skipped: 0.',
                'markTestIncomplete',
                \PHPUnit\Framework\IncompleteTestError::class
            ],
            [
                'Passed: 0, Failed: 0, Incomplete: 0, Skipped: 1.',
                'markTestSkipped',
                \PHPUnit\Framework\SkippedTestError::class
            ]
        ];
    }
}
