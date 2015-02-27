<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

class InvalidEmailOrPasswordExceptionTest extends \PHPUnit_Framework_TestCase
{
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
