<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Matcher;

/**
 * Class MethodInvokedAtIndex
 * Matches invocations per 'method' at 'position'
 * Example:
 * $mock->expects(new MethodInvokedAtIndex(0))->method('getMethod')->willReturn(1);
 * $mock->expects(new MethodInvokedAtIndex(1))->method('getMethod')->willReturn(2);
 *
 * $mock->getMethod(); // returns 1
 * $mock->getMethod(); // returns 2
 *
 * @package Magento\TestFramework\Matcher
 */
class MethodInvokedAtIndex extends \PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex
{
    /**
     * @var array
     */
    protected $indexes = [];

    /**
     * @param  \PHPUnit_Framework_MockObject_Invocation $invocation
     * @return boolean
     */
    public function matches(\PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if (!isset($this->indexes[$invocation->methodName])) {
            $this->indexes[$invocation->methodName] = 0;
        } else {
            $this->indexes[$invocation->methodName]++;
        }
        $this->currentIndex++;

        return $this->indexes[$invocation->methodName] == $this->sequenceIndex;
    }
}
