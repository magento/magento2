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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Design_Package_ProxyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Package_Proxy
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_packageMock;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager');
        $this->_packageMock = $this->getMock('Mage_Core_Model_Design_Package', array(), array(), '', false);
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with('Mage_Core_Model_Design_Package')
            ->will($this->returnValue($this->_packageMock));
        $this->_model = new Mage_Core_Model_Design_Package_Proxy($this->_objectManager);
    }

    protected function tearDown()
    {
        $this->_objectManager = null;
        $this->_model = null;
        $this->_packageMock = null;
    }

    public function testGetPublicFileUrl()
    {
        $this->_packageMock->expects($this->once())
            ->method('getPublicFileUrl')
            ->with('file', true)
            ->will($this->returnValue('return value'));
        $this->assertSame('return value', $this->_model->getPublicFileUrl('file', true));
    }

    public function testGetPublicDir()
    {
        $this->_packageMock->expects($this->once())
            ->method('getPublicDir')
            ->will($this->returnValue('return value'));
        $this->assertSame('return value', $this->_model->getPublicDir());
    }

    public function testMergeFiles()
    {
        $this->_packageMock->expects($this->once())
            ->method('mergeFiles')
            ->with(array('file1.css', 'file2.css'), 'css')
            ->will($this->returnValue('return value'));
        $this->assertSame('return value', $this->_model->mergeFiles(array('file1.css', 'file2.css'), 'css'));
    }
}
