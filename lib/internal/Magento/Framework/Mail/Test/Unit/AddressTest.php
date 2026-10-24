<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit;

use Magento\Framework\Mail\Address;
use PHPUnit\Framework\TestCase;

/**
 * test Magento\Framework\Mail\Address
 */
class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    protected $message;

    /**
     * Address object with nullable email parameter passed should not throw an exception.
     *
     * @return void
     */
    public function testGetEmailEmpty()
    {
        $address = new Address(null, "Test name");
        $this->assertNull($address->getEmail());
    }
}
