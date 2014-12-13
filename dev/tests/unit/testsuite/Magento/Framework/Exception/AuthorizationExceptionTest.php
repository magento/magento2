<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
