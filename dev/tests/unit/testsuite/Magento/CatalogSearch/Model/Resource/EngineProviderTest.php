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
 * @package     Magento_CatalogSearch
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Model\Resource;

class EngineProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $_model \Magento\CatalogSearch\Model\Resource\EngineProvider
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\Resource\EngineFactory
     */
    protected $_engineFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Store\Config
     */
    protected $_storeConfigMock;

    protected function setUp()
    {
        $this->_engineFactoryMock = $this->getMock('Magento\CatalogSearch\Model\Resource\EngineFactory',
            array('create'), array(), '', false);
        $this->_storeConfigMock = $this->getMock('Magento\Core\Model\Store\Config',
            array('getConfig'), array(), '', false);

        $this->_model = new \Magento\CatalogSearch\Model\Resource\EngineProvider($this->_engineFactoryMock,
            $this->_storeConfigMock);
    }

    public function testGetPositive()
    {
        $engineMock = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Fulltext\Engine',
            array('test', '__wakeup'),
            array(),
            '',
            false
        );
        $engineMock->expects($this->once())
            ->method('test')
            ->will($this->returnValue(true));

        $this->_storeConfigMock->expects($this->once())
            ->method('getConfig')
            ->with('catalog/search/engine')
            ->will($this->returnValue('Magento\CatalogSearch\Model\Resource\Fulltext\Engine'));

        $this->_engineFactoryMock->expects($this->once())
            ->method('create')
            ->with('Magento\CatalogSearch\Model\Resource\Fulltext\Engine')
            ->will($this->returnValue($engineMock));

        $this->assertEquals($engineMock, $this->_model->get());
    }

    public function testGetNegative()
    {
        $engineMock = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Fulltext\Engine', array('test', '__wakeup'), array(), '', false);
        $engineMock->expects($this->never())
            ->method('test');

        $this->_storeConfigMock->expects($this->once())
            ->method('getConfig')
            ->with('catalog/search/engine')
            ->will($this->returnValue(''));

        $this->_engineFactoryMock->expects($this->once())
            ->method('create')
            ->with('Magento\CatalogSearch\Model\Resource\Fulltext\Engine')
            ->will($this->returnValue($engineMock));

        $this->assertEquals($engineMock, $this->_model->get());
    }
}
