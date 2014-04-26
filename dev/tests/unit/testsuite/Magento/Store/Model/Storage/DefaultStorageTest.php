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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\Model\Storage;

/**
 * Test class for \Magento\Store\Model\Storage\DefaultStorage
 */
class DefaultStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultStorage
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_websiteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupMock;

    protected function setUp()
    {
        $this->_websiteMock = $this->getMock(
            'Magento\Store\Model\Website',
            array('getCode', 'getId', '__wakeup'),
            array(),
            '',
            false,
            false
        );
        $this->_groupMock = $this->getMock(
            'Magento\Store\Model\Group',
            array('getCode', 'getId', '__wakeup'),
            array(),
            '',
            false,
            false
        );
        $this->_storeFactoryMock = $this->getMock(
            'Magento\Store\Model\StoreFactory',
            array('create'),
            array(),
            '',
            false,
            false
        );
        $this->_websiteFactoryMock = $this->getMock(
            'Magento\Store\Model\WebsiteFactory',
            array('create'),
            array(),
            '',
            false,
            false
        );
        $this->_websiteFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_websiteMock)
        );
        $this->_groupFactoryMock = $this->getMock(
            'Magento\Store\Model\GroupFactory',
            array('create'),
            array(),
            '',
            false,
            false
        );
        $this->_groupFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_groupMock)
        );
        $this->_storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            array('setId', 'setCode', 'getCode', '__sleep', '__wakeup'),
            array(),
            '',
            false,
            false
        );
        $this->_storeFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_storeMock)
        );
        $this->_model = new DefaultStorage(
            $this->_storeFactoryMock,
            $this->_websiteFactoryMock,
            $this->_groupFactoryMock
        );
    }

    public function testHasSingleStore()
    {
        $this->assertEquals(false, $this->_model->hasSingleStore());
    }

    public function testGetStore()
    {
        $storeId = 'testStore';
        $this->assertInstanceOf('Magento\Store\Model\Store', $this->_model->getStore($storeId));
    }

    public function testGetStores()
    {
        $withDefault = true;
        $codeKey = true;
        $this->assertEquals(array(), $this->_model->getStores($withDefault, $codeKey));
    }

    public function testGetWebsite()
    {
        $websiteId = 'testWebsite';
        $this->assertInstanceOf('Magento\Store\Model\Website', $this->_model->getWebsite($websiteId));
        $this->assertSame($this->_websiteMock, $this->_model->getWebsite($this->_websiteMock));
    }

    public function testGetWebsiteEmptyString()
    {
        $websiteId = '';
        $this->assertInstanceOf('Magento\Store\Model\Website', $this->_model->getWebsite($websiteId));
    }

    public function testGetWebsitesWithDefault()
    {
        $withDefault = true;
        $codeKey = 'someKey';
        $this->_websiteMock->expects($this->once())->method('getCode')->will($this->returnValue(0));
        $this->_websiteMock->expects($this->never())->method('getId');
        $result = $this->_model->getWebsites($withDefault, $codeKey);
        $this->assertInstanceOf('Magento\Store\Model\Website', $result[0]);
    }

    public function testGetWebsitesWithoutDefault()
    {
        $withDefault = false;
        $codeKey = 'someKey';
        $this->_websiteMock->expects($this->never())->method('getCode');
        $this->_websiteMock->expects($this->never())->method('getId');
        $result = $this->_model->getWebsites($withDefault, $codeKey);
        $this->assertEquals(array(), $result);
    }

    public function testGetGroup()
    {
        $groupId = 'testGroup';
        $this->assertInstanceOf('Magento\Store\Model\Group', $this->_model->getGroup($groupId));

        $this->assertSame($this->_groupMock, $this->_model->getGroup($this->_groupMock));
    }

    public function testGetGroupsWithDefault()
    {
        $withDefault = true;
        $codeKey = 'someKey';
        $this->_groupMock->expects($this->once())->method('getCode')->will($this->returnValue(0));
        $this->_groupMock->expects($this->never())->method('getId');
        $result = $this->_model->getGroups($withDefault, $codeKey);
        $this->assertInstanceOf('Magento\Store\Model\Group', $result[0]);
    }

    public function testGetGroupsWithoutDefault()
    {
        $withDefault = false;
        $codeKey = 'someKey';
        $this->_groupMock->expects($this->never())->method('getCode');
        $this->_groupMock->expects($this->never())->method('getId');
        $result = $this->_model->getGroups($withDefault, $codeKey);
        $this->assertEquals(array(), $result);
    }

    public function testGetDefaultStoreView()
    {
        $this->assertNull($this->_model->getDefaultStoreView());
    }
}
