<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Helper;

/**
 * Class implements tests for \Magento\Webapi\Helper\Data class.
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Helper\Data */
    protected $_helper;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject('Magento\Webapi\Helper\Data');
        parent::setUp();
    }

    /**
     * Test identifying service name parts including subservices using class name.
     *
     * @dataProvider serviceNamePartsDataProvider
     */
    public function testGetServiceNameParts($className, $preserveVersion, $expected)
    {
        $actual = $this->_helper->getServiceNameParts($className, $preserveVersion);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Dataprovider for serviceNameParts
     *
     * @return array
     */
    public function serviceNamePartsDataProvider()
    {
        return [
            ['Magento\Customer\Api\AccountManagementInterface', false, ['CustomerAccountManagement']],
            [
                'Vendor\Customer\Api\AddressRepositoryInterface',
                true,
                ['VendorCustomerAddressRepository', 'V1']
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider dataProviderForTestGetServiceNamePartsInvalidName
     */
    public function testGetServiceNamePartsInvalidName($interfaceClassName)
    {
        $this->_helper->getServiceNameParts($interfaceClassName);
    }

    public function dataProviderForTestGetServiceNamePartsInvalidName()
    {
        return [
            ['BarV1Interface'], // Missed vendor, module, 'Service'
            ['Service\\V1Interface'], // Missed vendor and module
            ['Magento\\Foo\\Service\\BarVxInterface'], // Version number should be a number
            ['Magento\\Foo\\Service\\BarInterface'], // Version missed
            ['Magento\\Foo\\Service\\BarV1'], // 'Interface' missed
            ['Foo\\Service\\BarV1Interface'], // Module missed
            ['Foo\\BarV1Interface'] // Module and 'Service' missed
        ];
    }
}

require_once realpath(__DIR__ . '/../_files/test_interfaces.php');
