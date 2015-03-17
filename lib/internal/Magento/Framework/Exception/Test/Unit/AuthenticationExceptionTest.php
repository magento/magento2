<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Phrase;

/**
 * Class AuthenticationExceptionTest
 *
 * @package Magento\Framework\Exception
 */
class AuthenticationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $authenticationException = new AuthenticationException(
            new Phrase(
                AuthenticationException::AUTHENTICATION_ERROR,
                ['consumer_id' => 1, 'resources' => 'record2']
            )
        );
        $this->assertSame('An authentication error occurred.', $authenticationException->getMessage());
    }
}
