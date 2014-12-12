<?php
/**
 * Invalid state exception
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Exception\State;

class InvalidTransitionExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $instanceClass = 'Magento\Framework\Exception\State\InvalidTransitionException';
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $stateException = new InvalidTransitionException($message, $params, $cause);
        $this->assertInstanceOf($instanceClass, $stateException);
    }
}
