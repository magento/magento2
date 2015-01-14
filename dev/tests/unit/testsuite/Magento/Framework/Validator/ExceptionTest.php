<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Exception\InputException;

/**
 * Test case for \Magento\Framework\Validator\ValidatorException
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing \Magento\Framework\Validator\ValidatorException::getMessage
     */
    public function testGetMessage()
    {
        $expectedMessage = 'error1' . PHP_EOL . 'error2' . PHP_EOL . 'error3';
        $messages = ['field1' => ['error1', 'error2'], 'field2' => ['error3']];
        $exception = new \Magento\Framework\Validator\ValidatorException(
            InputException::DEFAULT_MESSAGE,
            [],
            null,
            $messages
        );
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
}
