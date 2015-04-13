<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

class GuestCartManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Api\GuestCartManagementInterface
     */
    protected $guestCart;

    public function setUp()
    {
        $this->guestCart = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Quote\Api\GuestCartManagementInterface'
        );
    }

    public function testCreateEmptyCart()
    {
        $cartId = $this->guestCart->createEmptyCart();
        $this->assertNotEmpty($cartId);
    }
}
