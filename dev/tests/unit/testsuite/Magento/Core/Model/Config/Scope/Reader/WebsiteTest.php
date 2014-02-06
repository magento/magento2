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
namespace Magento\Core\Model\Config\Scope\Reader;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Scope\Reader\Website
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_initialConfigMock;

    /**
     * @var \Magento\App\Config\ScopePool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopePullMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    protected function setUp()
    {
        $this->_initialConfigMock = $this->getMock('Magento\App\Config\Initial', array(), array(), '', false);
        $this->_scopePullMock = $this->getMock('Magento\App\Config\ScopePool', array(), array(), '', false);
        $this->_collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory',
            array('create'),
            array(),
            '',
            false
        );
        $websiteFactoryMock = $this->getMock('Magento\Core\Model\WebsiteFactory', array('create'), array(), '', false);
        $this->_websiteMock = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);
        $websiteFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_websiteMock));

        $this->_appStateMock = $this->getMock('Magento\App\State', array(), array(), '', false);
        $this->_appStateMock->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));

        $this->_model = new \Magento\Core\Model\Config\Scope\Reader\Website(
            $this->_initialConfigMock,
            $this->_scopePullMock,
            new \Magento\App\Config\Scope\Converter(),
            $this->_collectionFactory,
            $websiteFactoryMock,
            $this->_appStateMock
        );
    }

    public function testRead()
    {
        $websiteCode = 'default';
        $websiteId = 1;

        $dataMock = $this->getMock('Magento\App\Config\Data', array(), array(), '', false);
        $dataMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(array(
            'config' => array('key0' => 'default_value0', 'key1' => 'default_value1'),
        )));
        $dataMock->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue(array(
            'config' => array('key0' => 'default_value0', 'key1' => 'default_value1'),
        )));
        $this->_scopePullMock->expects($this->once())
            ->method('getScope')
            ->with('default', null)
            ->will($this->returnValue($dataMock));

        $this->_initialConfigMock->expects($this->any())
            ->method('getData')
            ->with("websites|{$websiteCode}")
            ->will($this->returnValue(array(
                'config' => array('key1' => 'website_value1', 'key2' => 'website_value2'),
            )));
        $this->_websiteMock->expects($this->once())
            ->method('load')
            ->with($websiteCode);
        $this->_websiteMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($websiteId));
        $this->_collectionFactory->expects($this->once())
            ->method('create')
            ->with(array('scope' => 'websites', 'scopeId' => $websiteId))
            ->will($this->returnValue(array(
                new \Magento\Object(array('path' => 'config/key1', 'value' => 'website_db_value1')),
                new \Magento\Object(array('path' => 'config/key3', 'value' => 'website_db_value3')),
            )));
        $expectedData = array(
            'config' => array(
                'key0' => 'default_value0', // value from default section
                'key1' => 'website_db_value1',
                'key2' => 'website_value2', // value that has not been overridden in DB
                'key3' => 'website_db_value3'
            ),
        );
        $this->assertEquals($expectedData, $this->_model->read($websiteCode));
    }
}
