<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * setUp all mocks and data function
     */
    public function setUp()
    {
        $filesystemMock =
            $this->getMock('Magento\Framework\Filesystem', ['getDirectoryRead'], [], '', false);
        $this->_coreConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_cacheState = $this->getMockForAbstractClass('Magento\Framework\App\Cache\StateInterface');

        $modulesDirectoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            [],
            [],
            '',
            false
        );
        $filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            DirectoryList::MODULES
        )->will(
            $this->returnValue($modulesDirectoryMock)
        );
        $modulesDirectoryMock->expects(
            $this->any()
        )->method(
            'readFile'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/test.vcl'))
        );
        $this->_coreConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnValueMap(
                [
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_BACKEND_HOST,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        'example.com',
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_BACKEND_PORT,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        '8080'
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_ACCESS_LIST,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        '127.0.0.1, 192.168.0.1'
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        serialize([['regexp' => '(?i)pattern', 'value' => 'value_for_pattern']])
                    ],
                ]
            )
        );

        $this->_model = new \Magento\PageCache\Model\Config(
            $filesystemMock,
            $this->_coreConfigMock,
            $this->_cacheState
        );
    }

    /**
     * test for getVcl method
     */
    public function testGetVcl()
    {
        $test = $this->_model->getVclFile();
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/result.vcl'), $test);
    }

    public function testGetTll()
    {
        $this->_coreConfigMock->expects($this->once())->method('getValue')->with(Config::XML_PAGECACHE_TTL);
        $this->_model->getTtl();
    }

    /**
     * Whether a cache type is enabled
     */
    public function testIsEnabled()
    {
        $this->_cacheState->expects($this->at(0))
            ->method('isEnabled')
            ->with(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)
            ->will($this->returnValue(true));
        $this->_cacheState->expects($this->at(1))
            ->method('isEnabled')
            ->with(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)
            ->will($this->returnValue(false));
        $this->assertTrue($this->_model->isEnabled());
        $this->assertFalse($this->_model->isEnabled());
    }
}
