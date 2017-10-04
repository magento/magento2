<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Address;

class AddressDataGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private $addressStructure = [
        'postcode',
    ];

    /**
     * @var \Magento\Setup\Model\Address\AddressDataGenerator
     */
    private $addressGenerator;

    public function setUp()
    {
        $this->addressGenerator = new \Magento\Setup\Model\Address\AddressDataGenerator();
    }

    public function testPostcode()
    {
        mt_srand(42);
        $address1 = $this->addressGenerator->generateAddress();

        mt_srand(66);
        $address2 = $this->addressGenerator->generateAddress();

        $this->assertNotEquals($address1['postcode'], $address2['postcode']);
    }

    public function testAddressStructure()
    {
        $address = $this->addressGenerator->generateAddress();

        foreach ($this->addressStructure as $addressField) {
            $this->assertTrue(array_key_exists($addressField, $address));
        }
    }
}
