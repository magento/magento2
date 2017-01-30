<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Phrase;

/**
 * Class InvalidEmailOrPasswordExceptionTest
 *
 * @package Magento\Framework\Exception
 */
class InvalidEmailOrPasswordExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $exception = new InvalidEmailOrPasswordException(
            new Phrase(
                InvalidEmailOrPasswordException::INVALID_EMAIL_OR_PASSWORD,
                ['consumer_id' => 1, 'resources' => 'record2']
            )
        );
        $this->assertSame('Invalid email or password', $exception->getMessage());
    }
}
