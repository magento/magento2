<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Model;

use \Magento\Wishlist\Model\AuthenticationState;

class AuthenticationStateTest extends \PHPUnit\Framework\TestCase
{
    public function testIsEnabled()
    {
        $this->assertTrue((new AuthenticationState())->isEnabled());
    }
}
