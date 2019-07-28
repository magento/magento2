<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
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
        $readFactoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $this->coreConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->cacheState = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\StateInterface::class);

        $modulesDirectoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
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
                        'serializedConfig'
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

        $this->moduleReader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->serializerMock = $this->createMock(Json::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject $vclTemplateLocator */
        $vclTemplateLocator = $this->getMockBuilder(\Magento\PageCache\Model\Varnish\VclTemplateLocator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTemplate'])
            ->getMock();
        $vclTemplateLocator->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue(file_get_contents(__DIR__ . '/_files/test.vcl')));
        /** @var \PHPUnit_Framework_MockObject_MockObject $vclTemplateLocator */
        $vclGeneratorFactory = $this->getMockBuilder(\Magento\PageCache\Model\Varnish\VclGeneratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $expectedParams = [
            'backendHost' => 'example.com',
            'backendPort' => '8080',
            'accessList' =>  explode(',', '127.0.0.1, 192.168.0.1,127.0.0.2'),
            'designExceptions' => [['regexp' => '(?i)pattern', 'value' => 'value_for_pattern']],
            'sslOffloadedHeader' => 'X_Forwarded_Proto: https',
            'gracePeriod' => 120
        ];
        $vclGeneratorFactory->expects($this->any())
            ->method('create')
            ->with($expectedParams)
            ->will($this->returnValue(new \Magento\PageCache\Model\Varnish\VclGenerator(
                $vclTemplateLocator,
                'example.com',
                '8080',
                explode(',', '127.0.0.1,192.168.0.1,127.0.0.2'),
                120,
                'X_Forwarded_Proto: https',
                [['regexp' => '(?i)pattern', 'value' => 'value_for_pattern']]
            )));
        $this->config = $objectManager->getObject(
            \Magento\PageCache\Model\Config::class,
            [
                'readFactory' => $readFactoryMock,
                'scopeConfig' => $this->coreConfigMock,
                'cacheState' => $this->cacheState,
                'reader' => $this->moduleReader,
                'serializer' => $this->serializerMock,
                'vclGeneratorFactory' => $vclGeneratorFactory
            ]
        );
    }

    /**
     * test for getVcl method
     */
    public function testGetVcl()
    {
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedConfig')
            ->willReturn([['regexp' => '(?i)pattern', 'value' => 'value_for_pattern']]);
        $test = $this->config->getVclFile(Config::VARNISH_5_CONFIGURATION_PATH);
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
