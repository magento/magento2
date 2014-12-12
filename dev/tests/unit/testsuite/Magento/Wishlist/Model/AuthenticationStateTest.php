<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Wishlist\Model;

class AuthenticationStateTest extends \PHPUnit_Framework_TestCase
{
    public function testIsEnabled()
    {
        $this->assertTrue((new AuthenticationState())->isEnabled());
    }
}
