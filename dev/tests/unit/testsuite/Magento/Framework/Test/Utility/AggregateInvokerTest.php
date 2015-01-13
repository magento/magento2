<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Utility;

class AggregateInvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Test\Utility\AggregateInvoker
     */
    protected $_invoker;

    /**
     * @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_testCase;

    protected function setUp()
    {
        $this->_testCase = $this->getMock(
            'PHPUnit_Framework_Test',
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
                'PHPUnit_Framework_AssertionFailedError',
            ],
            ['Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.', 'fail', 'PHPUnit_Framework_OutputError'],
            [
                'Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.',
                'fail',
                'PHPUnit_Framework_ExpectationFailedException'
            ],
            [
                'Passed: 0, Failed: 0, Incomplete: 1, Skipped: 0.',
                'markTestIncomplete',
                'PHPUnit_Framework_IncompleteTestError'
            ],
            [
                'Passed: 0, Failed: 0, Incomplete: 0, Skipped: 1.',
                'markTestSkipped',
                'PHPUnit_Framework_SkippedTestError'
            ]
        ];
    }
}
