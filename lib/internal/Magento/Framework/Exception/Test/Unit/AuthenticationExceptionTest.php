<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Phrase;

/**
 * Class AuthenticationExceptionTest
 */
class AuthenticationExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $authenticationException = new AuthenticationException(
            new Phrase(
                'An authentication error occurred.',
                ['consumer_id' => 1, 'resources' => 'record2']
            )
        );
        $this->assertSame('An authentication error occurred.', $authenticationException->getMessage());
    }
}
