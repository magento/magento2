<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Exception\InputException;

/**
 * Test case for \Magento\Framework\Validator\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing \Magento\Framework\Validator\Exception::getMessage
     */
    public function testGetMessage()
    {
        $expectedMessage = 'error1' . PHP_EOL . 'error2' . PHP_EOL . 'error3';
        $messages = ['field1' => ['error1', 'error2'], 'field2' => ['error3']];
        $exception = new \Magento\Framework\Validator\Exception(
            null,
            null,
            $messages
        );
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
}
