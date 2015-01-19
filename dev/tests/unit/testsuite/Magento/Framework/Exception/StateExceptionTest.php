<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception;

/**
 * Class StateExceptionTest
 *
 * @package Magento\Framework\Exception
 */
class StateExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testStateExceptionInstance()
    {
        $instanceClass = 'Magento\Framework\Exception\StateException';
        $message = 'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $stateException = new StateException($message, $params, $cause);
        $this->assertInstanceOf($instanceClass, $stateException);
    }
}
