<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Annotation\ConfigFixture.
 */
namespace Magento\Test\Annotation;

class ConfigFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\ConfigFixture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->createPartialMock(
            \Magento\TestFramework\Annotation\ConfigFixture::class,
            ['_getConfigValue', '_setConfigValue']
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
