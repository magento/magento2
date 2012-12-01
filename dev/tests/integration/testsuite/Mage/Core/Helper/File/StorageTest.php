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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Helper_File_StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Helper_File_Storage
     */
    protected $_helper;

    public function setUp()
    {
        $this->_helper = Mage::helper('Mage_Core_Helper_File_Storage');
    }

    protected function tearDown()
    {
        $this->_helper = null;
    }

    /**
     * @covers Mage_Core_Helper_File_Storage::getMaxFileSize
     * @backupStaticAttributes
     */
    public function testGetMaxFileSize()
    {
        $this->assertGreaterThanOrEqual(0, $this->_helper->getMaxFileSize());
        $this->assertGreaterThanOrEqual(0, $this->_helper->getMaxFileSizeInMb());
    }

    /**
     * @covers Mage_Core_Helper_File_Storage::_convertIniToInteger
     * @dataProvider getConvertIniToIntegerDataProvider
     * @backupStaticAttributes
     * @param string $arguments
     * @param int $expected
     */
    public function testConvertIniToInteger($arguments, $expected)
    {
        $class = new ReflectionClass('Mage_Core_Helper_File_Storage');
        $method = $class->getMethod('_convertIniToInteger');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invokeArgs($this->_helper, array($arguments)));
    }

    /**
     * @return array
     */
    public function getConvertIniToIntegerDataProvider()
    {
        return array(
            array('0K', 0),
            array('123K', 125952),
            array('1K', 1024),
            array('1g', 1073741824),
            array('asdas', 0),
            array('1M', 1048576),
        );
    }
}
