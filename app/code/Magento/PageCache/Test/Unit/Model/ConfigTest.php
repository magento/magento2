<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    private $config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $coreConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Json
     */
    private $serializerMock;

    /**
     * setUp all mocks and data function
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $readFactoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadFactory::class,
            [],
            [],
            '',
            false
        );
        $this->coreConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->cacheState = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\StateInterface::class);

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
        $this->coreConfigMock->expects(
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
                        '127.0.0.1, 192.168.0.1,127.0.0.2'
                    ],
                    [
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        'serializedConfig'
                    ],
                ]
            )
        );

        $this->moduleReader = $this->getMock(\Magento\Framework\Module\Dir\Reader::class, [], [], '', false);
        $this->serializerMock = $this->getMock(Json::class, [], [], '', false);
        $this->config = $objectManager->getObject(
            \Magento\PageCache\Model\Config::class,
            [
                'readFactory' => $readFactoryMock,
                'scopeConfig' => $this->coreConfigMock,
                'cacheState' => $this->cacheState,
                'reader' => $this->moduleReader,
                'serializer' => $this->serializerMock,
            ]
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
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedConfig')
            ->willReturn([['regexp' => '(?i)pattern', 'value' => 'value_for_pattern']]);
        $test = $this->config->getVclFile(Config::VARNISH_3_CONFIGURATION_PATH);
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/result.vcl'), $test);
    }

    public function testGetTll()
    {
        $this->coreConfigMock->expects($this->once())->method('getValue')->with(Config::XML_PAGECACHE_TTL);
        $this->config->getTtl();
    }

    /**
     * Whether a cache type is enabled
     */
    public function testIsEnabled()
    {
        $this->cacheState->expects($this->at(0))
            ->method('isEnabled')
            ->with(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)
            ->will($this->returnValue(true));
        $this->cacheState->expects($this->at(1))
            ->method('isEnabled')
            ->with(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)
            ->will($this->returnValue(false));
        $this->assertTrue($this->config->isEnabled());
        $this->assertFalse($this->config->isEnabled());
    }
}
