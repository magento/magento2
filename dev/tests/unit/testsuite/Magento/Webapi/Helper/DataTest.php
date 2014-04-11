<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return array(
            array('Magento\Customer\Service\V1\Customer\AddressInterface', false, array('Customer', 'Address')),
            array(
                'Vendor\Customer\Service\V1\Customer\AddressInterface',
                true,
                array('VendorCustomer', 'Address', 'V1')
            ),
            array('Magento\Catalog\Service\V2\ProductInterface', true, array('CatalogProduct', 'V2'))
        );
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
        return array(
            array('BarV1Interface'), // Missed vendor, module, 'Service'
            array('Service\\V1Interface'), // Missed vendor and module
            array('Magento\\Foo\\Service\\BarVxInterface'), // Version number should be a number
            array('Magento\\Foo\\Service\\BarInterface'), // Version missed
            array('Magento\\Foo\\Service\\BarV1'), // 'Interface' missed
            array('Foo\\Service\\BarV1Interface'), // Module missed
            array('Foo\\BarV1Interface') // Module and 'Service' missed
        );
    }
}

require_once realpath(__DIR__ . '/../_files/test_interfaces.php');
