<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Helper\Address */
    protected $helper;

    protected function setUp()
    {
        $this->helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Customer\Helper\Address'
        );
    }

    /**
     * @param $attributeCode
     * @dataProvider getAttributeValidationClass
     */
    public function testGetAttributeValidationClass($attributeCode, $expectedClass)
    {
        $this->assertEquals($expectedClass, $this->helper->getAttributeValidationClass($attributeCode));
    }

    public function getAttributeValidationClass()
    {
        return [
            ['bad-code', ''],
            ['city', ' required-entry'],
            ['company', ''],
            ['country_id', ' required-entry'],
            ['fax', ''],
            ['firstname', 'required-entry'],
            ['lastname', 'required-entry'],
            ['middlename', ''],
            ['postcode', '']
        ];
    }
}
