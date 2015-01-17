<?php
/**
 * Expired exception
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception\State;

/**
 * Class ExpiredException
 *
 * @package Magento\Framework\Exception\State
 */
class ExpiredExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $instanceClass = 'Magento\Framework\Exception\State\ExpiredException';
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $stateException = new ExpiredException($message, $params, $cause);
        $this->assertInstanceOf($instanceClass, $stateException);
    }
}
