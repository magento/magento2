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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Page_Asset_ViewFileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Page_Asset_ViewFile
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_designPackage;

    protected function setUp()
    {
        $this->_designPackage = $this->getMock(
            'Mage_Core_Model_Design_Package', array('getViewFileUrl'), array(), '', false
        );
        $this->_object = new Mage_Core_Model_Page_Asset_ViewFile($this->_designPackage, 'test/script.js', 'js');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter 'file' must not be empty
     */
    public function testConstructorException()
    {
        new Mage_Core_Model_Page_Asset_ViewFile($this->_designPackage, '', 'unknown');
    }

    public function testGetUrl()
    {
        $url = 'http://127.0.0.1/magento/test/script.js';
        $this->_designPackage
            ->expects($this->once())
            ->method('getViewFileUrl')
            ->with('test/script.js')
            ->will($this->returnValue($url))
        ;
        $this->assertEquals($url, $this->_object->getUrl());
    }

    public function testGetContentType()
    {
        $this->assertEquals('js', $this->_object->getContentType());
    }

    public function testGetSourceFile()
    {
        $this->assertEquals('test/script.js', $this->_object->getSourceFile());
    }
}
