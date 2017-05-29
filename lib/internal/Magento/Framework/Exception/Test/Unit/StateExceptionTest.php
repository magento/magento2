<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\StateException;
use Magento\Framework\Phrase;

/**
 * Class StateExceptionTest
 */
class StateExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testStateExceptionInstance()
    {
        $instanceClass = \Magento\Framework\Exception\StateException::class;
        $message = 'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $stateException = new StateException(new Phrase($message, $params), $cause);
        $this->assertInstanceOf($instanceClass, $stateException);
    }
}
