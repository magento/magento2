<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Model;

use \Magento\Wishlist\Model\AuthenticationState;

class AuthenticationStateTest extends \PHPUnit_Framework_TestCase
{
    public function testIsEnabled()
    {
        $this->assertTrue((new AuthenticationState())->isEnabled());
    }
}
