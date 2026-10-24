<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Wishlist\Model\AuthenticationState;
use PHPUnit\Framework\TestCase;

class AuthenticationStateTest extends TestCase
{
    public function testIsEnabled()
    {
        $this->assertTrue((new AuthenticationState())->isEnabled());
    }
}
