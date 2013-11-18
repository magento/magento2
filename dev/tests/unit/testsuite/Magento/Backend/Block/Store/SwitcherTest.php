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
 * @package     Magento_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Block\Store;

class SwitcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Store\Switcher
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_websiteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeGroupMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeFactoryMock;

    protected function setUp()
    {
        $this->_coreHelperMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_contextMock = $this->getMock('Magento\Backend\Block\Template\Context', array(), array(), '', false);
        $this->_applicationMock = $this->getMock('Magento\Core\Model\App', array(), array(), '', false);
        $this->_websiteFactoryMock = $this->getMock('Magento\Core\Model\Website\Factory', array(), array(), '', false);
        $this->_storeGroupMock = $this->getMock('Magento\Core\Model\Store\Group\Factory', array(), array(), '', false);
        $this->_storeFactoryMock = $this->getMock('Magento\Core\Model\StoreFactory', array(), array(), '', false);

        $this->_object = new \Magento\Backend\Block\Store\Switcher(
            $this->_coreHelperMock,
            $this->_contextMock,
            $this->_applicationMock,
            $this->_websiteFactoryMock,
            $this->_storeGroupMock,
            $this->_storeFactoryMock
        );
    }

    /**
     * @covers \Magento\Backend\Block\Store\Switcher::getWebsiteCollection
     */
    public function testGetWebsiteCollectionWhenWebSiteIdsEmpty()
    {
        $websiteModel = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false, false);
        $collection = $this->getMock(
            'Magento\Core\Model\Resource\Website\Collection', array(), array(), '', false, false
        );
        $websiteModel->expects($this->once())->method('getResourceCollection')->will($this->returnValue($collection));

        $expected = array('test', 'data', 'some');
        $collection->expects($this->once())->method('load')->will($this->returnValue($expected));
        $collection->expects($this->never())->method('addIdFilter');

        $this->_websiteFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($websiteModel));

        $this->_object->setWebsiteIds(null);

        $actual = $this->_object->getWebsiteCollection();
        $this->assertEquals($expected, $actual);
    }


    /**
     * @covers \Magento\Backend\Block\Store\Switcher::getWebsiteCollection
     */
    public function testGetWebsiteCollectionWhenWebSiteIdsIsSet()
    {
        $websiteModel = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false, false);
        $collection = $this->getMock(
            'Magento\Core\Model\Resource\Website\Collection',
            array(),
            array(),
            '',
            false,
            false
        );
        $websiteModel->expects($this->once())->method('getResourceCollection')->will($this->returnValue($collection));

        $ids = array(1, 2, 3);
        $this->_object->setWebsiteIds($ids);

        $expected = array('test', 'data', 'some');
        $collection->expects($this->once())->method('load')->will($this->returnValue($expected));
        $collection->expects($this->once())->method('addIdFilter')->with($ids);

        $this->_websiteFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($websiteModel));

        $actual = $this->_object->getWebsiteCollection();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \Magento\Backend\Block\Store\Switcher::getWebsites
     */
    public function testGetWebsitesWhenWebSiteIdsIsNotSet()
    {
        $this->_object->setWebsiteIds(null);

        $expected = array('test', 'data', 'some');
        $this->_applicationMock->expects($this->once())->method('getWebsites')->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->_object->getWebsites());
    }

    /**
     * @covers \Magento\Backend\Block\Store\Switcher::getWebsites
     */
    public function testGetWebsitesWhenWebSiteIdsIsSetAndMatchWebsites()
    {
        $ids = array(1, 3, 5);
        $webSites = array(
            1 => 'site 1',
            2 => 'site 2',
            3 => 'site 3',
            4 => 'site 4',
            5 => 'site 5',
        );

        $this->_object->setWebsiteIds($ids);

        $expected = array(
            1 => 'site 1',
            3 => 'site 3',
            5 => 'site 5',
        );
        $this->_applicationMock->expects($this->once())->method('getWebsites')->will($this->returnValue($webSites));

        $this->assertEquals($expected, $this->_object->getWebsites());
    }

    /**
     * @covers \Magento\Backend\Block\Store\Switcher::getWebsites
     */
    public function testGetWebsitesWhenWebSiteIdsIsSetAndNotMatchWebsites()
    {
        $ids = array(8, 10, 12);
        $webSites = array(
            1 => 'site 1',
            2 => 'site 2',
            3 => 'site 3',
            4 => 'site 4',
            5 => 'site 5',
        );

        $this->_object->setWebsiteIds($ids);

        $expected = array();
        $this->_applicationMock->expects($this->once())->method('getWebsites')->will($this->returnValue($webSites));

        $this->assertEquals($expected, $this->_object->getWebsites());
    }
}
