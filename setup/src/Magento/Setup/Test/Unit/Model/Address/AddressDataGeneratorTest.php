<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Address;

use Magento\Setup\Model\Address\AddressDataGenerator;
use PHPUnit\Framework\TestCase;

class AddressDataGeneratorTest extends TestCase
{
    /**
     * @var array
     */
    private $addressStructure = [
        'postcode',
    ];

    /**
     * @var AddressDataGenerator
     */
    private $addressGenerator;

    protected function setUp(): void
    {
        $this->addressGenerator = new AddressDataGenerator();
    }

    public function testPostcode()
    {
        // phpcs:ignore
        mt_srand(42);
        $address1 = $this->addressGenerator->generateAddress();

        // phpcs:ignore
        mt_srand(66);
        $address2 = $this->addressGenerator->generateAddress();

        $this->assertNotEquals($address1['postcode'], $address2['postcode']);
    }

    public function testAddressStructure()
    {
        $address = $this->addressGenerator->generateAddress();

        foreach ($this->addressStructure as $addressField) {
            $this->assertArrayHasKey($addressField, $address);
        }
    }
}
