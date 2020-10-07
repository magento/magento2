<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Validator\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test case for \Magento\Framework\Validator\Exception
 */
class ExceptionTest extends TestCase
{
    /**
     * Testing \Magento\Framework\Validator\Exception::getMessage
     * @return void
     */
    public function testGetMessage()
    {
        $expectedMessage = 'error1' . PHP_EOL . 'error2' . PHP_EOL . 'error3';
        $messages = ['field1' => ['error1', 'error2'], 'field2' => ['error3']];
        $exception = new Exception(
            null,
            null,
            $messages
        );
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
}
