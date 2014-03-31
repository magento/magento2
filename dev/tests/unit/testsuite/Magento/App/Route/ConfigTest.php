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
namespace Magento\App\Route;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Route\Config
     */
    protected $_config;

    /**
     * @var Cache_Mock_Wrapper
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_areaList;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock('Magento\App\Route\Config\Reader', array(), array(), '', false);
        $this->_cacheMock = $this->getMock('Magento\Config\CacheInterface');
        $this->_configScopeMock = $this->getMock('\Magento\Config\ScopeInterface');
        $this->_areaList = $this->getMock('\Magento\App\AreaList', array(), array(), '', false);
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'getCurrentScope'
        )->will(
            $this->returnValue('areaCode')
        );
        $this->_config = new \Magento\App\Route\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_configScopeMock,
            $this->_areaList
        );
    }

    public function testGetRouteFrontNameIfCacheIfRouterIdNotExist()
    {
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'areaCode::RoutesConfig'
        )->will(
            $this->returnValue(serialize(array('expected')))
        );
        $this->assertEquals('routerCode', $this->_config->getRouteFrontName('routerCode'));
    }
}
