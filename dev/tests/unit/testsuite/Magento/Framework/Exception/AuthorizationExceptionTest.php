<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class AuthorizationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $authorizationException = new AuthorizationException(
            AuthorizationException::NOT_AUTHORIZED,
            ['consumer_id' => 1, 'resources' => 'record2']
        );
        $this->assertSame('Consumer is not authorized to access record2', $authorizationException->getMessage());
    }
}
