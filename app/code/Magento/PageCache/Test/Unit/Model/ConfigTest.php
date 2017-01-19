<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model;

use Magento\PageCache\Model\Config;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * setUp all mocks and data function
     */
    protected function setUp()
    {
        $readFactoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadFactory::class,
            [],
            [],
            '',
            false
        );
        $this->_coreConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_cacheState = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\StateInterface::class);

        $modulesDirectoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            [],
            [],
            '',
            false
        );
        $readFactoryMock->expects(
            $this->any()
        )->method(
            'create'
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
                        \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'example.com',
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_BACKEND_PORT,
                        \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        '8080'
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_ACCESS_LIST,
                        \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        '127.0.0.1, 192.168.0.1,127.0.0.2'
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        serialize([['regexp' => '(?i)pattern', 'value' => 'value_for_pattern']])
                    ],
                    [
                        \Magento\Framework\HTTP\PhpEnvironment\Request::XML_PATH_OFFLOADER_HEADER,
                        \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'X_Forwarded_Proto: https'
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_GRACE_PERIOD,
                        \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        120
                    ],
                ]
            )
        );

        $this->moduleReader = $this->getMock(\Magento\Framework\Module\Dir\Reader::class, [], [], '', false);
        $this->_model = new \Magento\PageCache\Model\Config(
            $readFactoryMock,
            $this->_coreConfigMock,
            $this->_cacheState,
            $this->moduleReader
        );
    }

    /**
     * test for getVcl method
     */
    public function testGetVcl()
    {
        $this->moduleReader->expects($this->once())
            ->method('getModuleDir')
            ->willReturn('/magento/app/code/Magento/PageCache');
        $test = $this->_model->getVclFile(Config::VARNISH_5_CONFIGURATION_PATH);
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
