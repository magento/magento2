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
namespace Magento\Store\App\Action\Plugin;

class StoreCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\App\Action\Plugin\StoreCheck
     */
    protected $_plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->_storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );
        $this->subjectMock = $this->getMock('Magento\Framework\App\Action\Action', array(), array(), '', false);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');

        $this->_plugin = new \Magento\Store\App\Action\Plugin\StoreCheck($this->_storeManagerMock);
    }

    /**
     * @expectedException \Magento\Framework\App\InitException
     * @expectedExceptionMessage Current store is not active.
     */
    public function testAroundDispatchWhenStoreNotActive()
    {
        $this->_storeMock->expects($this->any())->method('getIsActive')->will($this->returnValue(false));
        $this->assertEquals(
            'Expected',
            $this->_plugin->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

    public function testAroundDispatchWhenStoreIsActive()
    {
        $this->_storeMock->expects($this->any())->method('getIsActive')->will($this->returnValue(true));
        $this->assertEquals(
            'Expected',
            $this->_plugin->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

}
