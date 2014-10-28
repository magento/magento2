<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework\Utility;

class AggregateInvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Utility\AggregateInvoker
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
        $this->_invoker = new AggregateInvoker($this->_testCase, array());
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
            array(array(0))
        );
    }

    /**
     * @return array
     */
    public function callbackDataProvider()
    {
        return array(
            array(
                'Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.',
                'fail',
                'PHPUnit_Framework_AssertionFailedError'
            ),
            array('Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.', 'fail', 'PHPUnit_Framework_OutputError'),
            array(
                'Passed: 0, Failed: 1, Incomplete: 0, Skipped: 0.',
                'fail',
                'PHPUnit_Framework_ExpectationFailedException'
            ),
            array(
                'Passed: 0, Failed: 0, Incomplete: 1, Skipped: 0.',
                'markTestIncomplete',
                'PHPUnit_Framework_IncompleteTestError'
            ),
            array(
                'Passed: 0, Failed: 0, Incomplete: 0, Skipped: 1.',
                'markTestSkipped',
                'PHPUnit_Framework_SkippedTestError'
            )
        );
    }
}
