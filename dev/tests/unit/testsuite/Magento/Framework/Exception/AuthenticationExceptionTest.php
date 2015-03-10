<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * Class AuthenticationExceptionTest
 *
 * @package Magento\Framework\Exception
 */
class AuthenticationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $authenticationException = new AuthenticationException(
            new Phrase(
                AuthenticationException::AUTHENTICATION_ERROR
            )
        );
        $this->assertSame('An authentication error occurred.', $authenticationException->getMessage());
    }
}
