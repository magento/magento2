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

/**
 * Test class for \Magento\TestFramework\Annotation\ConfigFixture.
 */
namespace Magento\Test\Annotation;

class ConfigFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\ConfigFixture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->getMock(
            'Magento\TestFramework\Annotation\ConfigFixture',
            array('_getConfigValue', '_setConfigValue')
        );
    }

    /**
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     */
    public function testGlobalConfig()
    {
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'web/unsecure/base_url'
        )->will(
            $this->returnValue('http://localhost/')
        );
        $this->_object->expects(
            $this->at(1)
        )->method(
            '_setConfigValue'
        )->with(
            'web/unsecure/base_url',
            'http://example.com/'
        );
        $this->_object->startTest($this);

        $this->_object->expects(
            $this->once()
        )->method(
            '_setConfigValue'
        )->with(
            'web/unsecure/base_url',
            'http://localhost/'
        );
        $this->_object->endTest($this);
    }

    /**
     * @magentoConfigFixture current_store dev/restrict/allow_ips 192.168.0.1
     */
    public function testCurrentStoreConfig()
    {
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'dev/restrict/allow_ips',
            ''
        )->will(
            $this->returnValue('127.0.0.1')
        );
        $this->_object->expects(
            $this->at(1)
        )->method(
            '_setConfigValue'
        )->with(
            'dev/restrict/allow_ips',
            '192.168.0.1',
            ''
        );
        $this->_object->startTest($this);

        $this->_object->expects(
            $this->once()
        )->method(
            '_setConfigValue'
        )->with(
            'dev/restrict/allow_ips',
            '127.0.0.1',
            ''
        );
        $this->_object->endTest($this);
    }

    /**
     * @magentoConfigFixture admin_store dev/restrict/allow_ips 192.168.0.2
     */
    public function testSpecificStoreConfig()
    {
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'dev/restrict/allow_ips',
            'admin'
        )->will(
            $this->returnValue('192.168.0.1')
        );
        $this->_object->expects(
            $this->at(1)
        )->method(
            '_setConfigValue'
        )->with(
            'dev/restrict/allow_ips',
            '192.168.0.2',
            'admin'
        );
        $this->_object->startTest($this);

        $this->_object->expects(
            $this->once()
        )->method(
            '_setConfigValue'
        )->with(
            'dev/restrict/allow_ips',
            '192.168.0.1',
            'admin'
        );
        $this->_object->endTest($this);
    }

    public function testInitStoreAfterOfScope()
    {
        $this->_object->expects($this->never())->method('_getConfigValue');
        $this->_object->expects($this->never())->method('_setConfigValue');
        $this->_object->initStoreAfter();
    }

    /**
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     */
    public function testInitStoreAfter()
    {
        $this->_object->startTest($this);
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'web/unsecure/base_url'
        )->will(
            $this->returnValue('http://localhost/')
        );
        $this->_object->expects(
            $this->at(1)
        )->method(
            '_setConfigValue'
        )->with(
            'web/unsecure/base_url',
            'http://example.com/'
        );
        $this->_object->initStoreAfter();
    }
}
