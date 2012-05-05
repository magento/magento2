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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Helper_HttpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Helper_Http
     */
    protected $_helper = null;

    public function setUp()
    {
        $this->_helper = new Mage_Core_Helper_Http;
    }

    public function testGetRemoteAddrHeaders()
    {
        $this->assertEquals(array(), $this->_helper->getRemoteAddrHeaders());
    }

    public function testGetRemoteAddr()
    {
        $this->assertEquals(false, $this->_helper->getRemoteAddr());
    }

    public function testGetServerAddr()
    {
        $this->assertEquals(false, $this->_helper->getServerAddr());
    }

    public function testGetHttpMethods()
    {
        $host = 'localhost';
        $this->assertEquals(false, $this->_helper->getHttpAcceptCharset());
        $this->assertEquals($host, $this->_helper->getHttpHost());
        $this->assertEquals(false, $this->_helper->getHttpReferer());
        $this->assertEquals(false, $this->_helper->getHttpAcceptLanguage());
        $this->assertEquals(false, $this->_helper->getHttpUserAgent());
    }

    public function testGetRequestUri()
    {
        $this->assertNull($this->_helper->getRequestUri());
    }

    public function testValidateIpAddr()
    {
        $this->assertTrue((bool)$this->_helper->validateIpAddr('127.0.0.1'));
        $this->assertFalse((bool)$this->_helper->validateIpAddr('invalid'));
    }
}
