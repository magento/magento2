<?php
/**
 * Config helper Unit tests.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Class implements tests for \Magento\Webapi\Model\Soap\Config class.
 */
namespace Magento\Webapi\Model\Soap;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Soap\Config */
    protected $_soapConfig;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManagerMock = $this->getMockBuilder('Magento\Core\Model\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $fileSystemMock = $this->getMockBuilder('Magento\Filesystem')->disableOriginalConstructor()->getMock();
        $dirMock = $this->getMockBuilder('Magento\App\Dir')->disableOriginalConstructor()->getMock();
        $configMock = $this->getMockBuilder('Magento\Webapi\Model\Config')->disableOriginalConstructor()->getMock();
        $this->_soapConfig = new \Magento\Webapi\Model\Soap\Config(
            $objectManagerMock,
            $fileSystemMock,
            $dirMock,
            $configMock
        );
        parent::setUp();
    }

    /**
     * Test identifying service name parts including subservices using class name.
     *
     * @dataProvider serviceNamePartsDataProvider
     */
    public function testGetServiceNameParts($className, $preserveVersion, $expected)
    {
        $actual = $this->_soapConfig->getServiceNameParts(
            $className,
            $preserveVersion
        );
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
            array('Magento\Customer\Service\Customer\AddressV1Interface', false, array('Customer', 'Address')),
            array(
                'Vendor\Customer\Service\Customer\AddressV1Interface',
                true,
                array('VendorCustomer', 'Address', 'V1')
            ),
            array('Magento\Catalog\Service\ProductV2Interface', true, array('CatalogProduct', 'V2'))
        );
    }
}
