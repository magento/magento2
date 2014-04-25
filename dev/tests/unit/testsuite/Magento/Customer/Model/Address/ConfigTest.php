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
namespace Magento\Customer\Model\Address;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_addressHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_cacheId = 'cache_id';

    public function setUp()
    {
        $this->_storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_readerMock = $this->getMock(
            'Magento\Customer\Model\Address\Config\Reader',
            array(),
            array(),
            '',
            false
        );
        $this->_cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );

        $this->_addressHelperMock = $this->getMock('Magento\Customer\Helper\Address', array(), array(), '', false);

        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );

        $fixtureConfigData = require __DIR__ . '/Config/_files/formats_merged.php';

        $this->_readerMock->expects($this->once())->method('read')->will($this->returnValue($fixtureConfigData));

        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            serialize($fixtureConfigData),
            $this->_cacheId
        );


        $this->_model = new \Magento\Customer\Model\Address\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_storeManagerMock,
            $this->_addressHelperMock,
            $this->_scopeConfigMock,
            $this->_cacheId
        );
    }

    public function testGetStore()
    {
        $this->assertEquals($this->_storeMock, $this->_model->getStore());
    }

    public function testSetStore()
    {
        $this->_model->setStore($this->_storeMock);

        //no call to $_storeManagerMock's method
        $this->assertEquals($this->_storeMock, $this->_model->getStore());
    }

    public function testGetFormats()
    {
        $this->_storeMock->expects($this->once())->method('getId');

        $this->_scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue('someValue'));

        $rendererMock = $this->getMock('Magento\Framework\Object');

        $this->_addressHelperMock->expects(
            $this->any()
        )->method(
            'getRenderer'
        )->will(
            $this->returnValue($rendererMock)
        );

        $firstExpected = new \Magento\Framework\Object();
        $firstExpected->setCode(
            'format_one'
        )->setTitle(
            'format_one_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            false
        )->setRenderer(
            null
        );

        $secondExpected = new \Magento\Framework\Object();
        $secondExpected->setCode(
            'format_two'
        )->setTitle(
            'format_two_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            true
        )->setRenderer(
            null
        );
        $expectedResult = array($firstExpected, $secondExpected);

        $this->assertEquals($expectedResult, $this->_model->getFormats());
    }
}
