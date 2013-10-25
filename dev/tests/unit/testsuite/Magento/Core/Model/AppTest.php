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
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Core\Model\App
 */
namespace Magento\Core\Model;

class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\App
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontControllerMock;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Magento\Core\Model\Config', array(), array(), '', false, false);
        $this->_cacheMock = $this->getMock('Magento\Core\Model\CacheInterface');
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager', array(), array(), '', false, false);
        $this->_storeManagerMock = $this->getMock('Magento\Core\Model\StoreManagerInterface');
        $this->_eventManagerMock = $this->getMock('Magento\Event\ManagerInterface');
        $this->_appStateMock = $this->getMock('Magento\App\State', array(), array(), '', false, false);
        $this->_configScopeMock = $this->getMock('Magento\Config\Scope', array(), array(), '', false);
        $this->_frontControllerMock = $this->getMock('Magento\App\FrontControllerInterface');

        $this->_model = new \Magento\Core\Model\App(
            $this->_configMock,
            $this->_cacheMock,
            $this->_objectManagerMock,
            $this->_storeManagerMock,
            $this->_eventManagerMock,
            $this->_appStateMock,
            $this->_configScopeMock,
            $this->_frontControllerMock
        );
    }

    protected function tearDown()
    {
        unset($this->_configMock);
        unset($this->_cacheMock);
        unset($this->_objectManagerMock);
        unset($this->_storeManagerMock);
        unset($this->_eventManagerMock);
        unset($this->_appStateMock);
        unset($this->_model);
    }

    public function testGetSafeStore()
    {
        $storeId = 'test';
        $this->_storeManagerMock->expects($this->once())
            ->method('getSafeStore')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getSafeStore($storeId));
    }

    public function testSetIsSingleStoreModeAllowed()
    {
        $value = true;
        $this->_storeManagerMock->expects($this->once())
            ->method('setIsSingleStoreModeAllowed')
            ->with($this->equalTo($value));
        $this->_model->setIsSingleStoreModeAllowed($value);
    }

    public function testHasSingleStore()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->hasSingleStore());
    }

    public function testIsSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->isSingleStoreMode());
    }

    public function testThrowStoreException()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('throwStoreException');
        $this->_model->throwStoreException();
    }

    public function testGetStore()
    {
        $storeId = 'some_value';
        $this->_storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getStore($storeId));
    }

    public function testGetStores()
    {
        $withDefault = true;
        $codeKey = true;
        $this->_storeManagerMock->expects($this->once())
            ->method('getStores')
            ->with($this->equalTo($withDefault),
                   $this->equalTo($codeKey))
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getStores($withDefault, $codeKey));
    }

    public function testGetWebsite()
    {
        $websiteId = 'some_value';
        $this->_storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with($this->equalTo($websiteId))
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getWebsite($websiteId));
    }

    public function testGetWebsites()
    {
        $withDefault = true;
        $codeKey = true;
        $this->_storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->with($this->equalTo($withDefault),
                   $this->equalTo($codeKey))
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getWebsites($withDefault, $codeKey));
    }

    public function testReinitStores()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('reinitStores');
        $this->_model->reinitStores();
    }

    public function testSetCurrentStore()
    {
        $store = 'Test';
        $this->_storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with($this->equalTo($store));
        $this->_model->setCurrentStore($store);
    }

    public function testGetDefaultStoreView()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getDefaultStoreView());
    }

    public function testGetGroup()
    {
        $groupId = 'test';
        $this->_storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->will($this->returnValue('proxy_result'))
            ->with($this->equalTo($groupId));
        $this->assertEquals('proxy_result', $this->_model->getGroup($groupId));
    }

    public function testGetGroups()
    {
        $withDefault = true;
        $codeKey = true;
        $this->_storeManagerMock->expects($this->once())
            ->method('getGroups')
            ->with($this->equalTo($withDefault),
            $this->equalTo($codeKey))
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getGroups($withDefault, $codeKey));
    }

    public function testClearWebsiteCache()
    {
        $websiteId = 'Test';
        $this->_storeManagerMock->expects($this->once())
            ->method('clearWebsiteCache')
            ->with($this->equalTo($websiteId));
        $this->_model->clearWebsiteCache($websiteId);
    }

    public function testGetAnyStoreView()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('getAnyStoreView')
            ->will($this->returnValue('proxy_result'));
        $this->assertEquals('proxy_result', $this->_model->getAnyStoreView());
    }
}
