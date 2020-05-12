<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Varnish\VclGenerator;
use Magento\PageCache\Model\Varnish\VclGeneratorFactory;
use Magento\PageCache\Model\Varnish\VclTemplateLocator;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $coreConfigMock;

    /**
     * @var MockObject|StateInterface
     */
    private $cacheState;

    /**
     * @var MockObject|Reader
     */
    private $moduleReader;

    /**
     * @var MockObject|Json
     */
    private $serializerMock;

    /**
     * setUp all mocks and data function
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $readFactoryMock = $this->createMock(ReadFactory::class);
        $this->coreConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->cacheState = $this->getMockForAbstractClass(StateInterface::class);

        $modulesDirectoryMock = $this->createMock(Write::class);
        $readFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $modulesDirectoryMock
        );
        $modulesDirectoryMock->expects(
            $this->any()
        )->method(
            'readFile'
        )->willReturn(
            file_get_contents(__DIR__ . '/_files/test.vcl')
        );
        $this->coreConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->willReturnMap(
            [
                [
                    Config::XML_VARNISH_PAGECACHE_BACKEND_HOST,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    'example.com',
                ],
                [
                    Config::XML_VARNISH_PAGECACHE_BACKEND_PORT,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    '8080'
                ],
                [
                    Config::XML_VARNISH_PAGECACHE_ACCESS_LIST,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    '127.0.0.1, 192.168.0.1,127.0.0.2'
                ],
                [
                    Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
                    ScopeInterface::SCOPE_STORE,
                    null,
                    'serializedConfig'
                ],
                [
                    Request::XML_PATH_OFFLOADER_HEADER,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    'X_Forwarded_Proto: https'
                ],
                [
                    Config::XML_VARNISH_PAGECACHE_GRACE_PERIOD,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    120
                ],
            ]
        );

        $this->moduleReader = $this->createMock(Reader::class);
        $this->serializerMock = $this->createMock(Json::class);

        /** @var MockObject $vclTemplateLocator */
        $vclTemplateLocator = $this->getMockBuilder(VclTemplateLocator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTemplate'])
            ->getMock();
        $vclTemplateLocator->expects($this->any())
            ->method('getTemplate')
            ->willReturn(file_get_contents(__DIR__ . '/_files/test.vcl'));
        /** @var MockObject $vclTemplateLocator */
        $vclGeneratorFactory = $this->getMockBuilder(VclGeneratorFactory::class)
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
            ->willReturn(new VclGenerator(
                $vclTemplateLocator,
                'example.com',
                '8080',
                explode(',', '127.0.0.1,192.168.0.1,127.0.0.2'),
                120,
                'X_Forwarded_Proto: https',
                [['regexp' => '(?i)pattern', 'value' => 'value_for_pattern']]
            ));
        $this->config = $objectManager->getObject(
            Config::class,
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
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheState->expects($this->at(1))
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);
        $this->assertTrue($this->config->isEnabled());
        $this->assertFalse($this->config->isEnabled());
    }
}
