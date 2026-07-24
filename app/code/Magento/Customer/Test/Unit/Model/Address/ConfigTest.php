<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Block\Address\Renderer\DefaultRenderer;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Address\Config\Reader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for config function.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $addressHelperMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Config
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = $this->createModel();
    }

    /**
     * @param class-string<Config> $configClass
     */
    private function createModel(string $configClass = Config::class): Config
    {
        $cacheId = 'cache_id';
        $objectManagerHelper = new ObjectManager($this);
        $this->storeMock = $this->createMock(Store::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $readerMock = $this->createMock(Reader::class);
        $cacheMock = $this->createMock(CacheInterface::class);
        $storeManagerMock = $this->createMock(StoreManager::class);
        $storeManagerMock->method('getStore')
            ->willReturnCallback(fn ($store = null) => $store ?? $this->storeMock);

        $this->addressHelperMock = $this->createMock(Address::class);

        $cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $cacheId
        )->willReturn(
            false
        );

        $fixtureConfigData = require __DIR__ . '/Config/_files/formats_merged.php';

        $readerMock->expects($this->once())->method('read')->willReturn($fixtureConfigData);

        $cacheMock->expects($this->once())
            ->method('save')
            ->with(
                json_encode($fixtureConfigData),
                $cacheId
            );

        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->method('serialize')
            ->willReturn(json_encode($fixtureConfigData));
        $serializerMock->method('unserialize')
            ->willReturn($fixtureConfigData);

        return $objectManagerHelper->getObject(
            $configClass,
            [
                'reader' => $readerMock,
                'cache' => $cacheMock,
                'storeManager' => $storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock,
                'cacheId' => $cacheId,
                'serializer' => $serializerMock,
                'addressHelper' => $this->addressHelperMock,
            ]
        );
    }

    /**
     * @return MockObject&\Magento\Customer\Block\Address\Renderer\RendererInterface
     */
    private function createRendererMock(): MockObject
    {
        $rendererMock = $this->createMock(\Magento\Customer\Block\Address\Renderer\RendererInterface::class);
        $rendererMock->method('setType')->willReturnSelf();

        return $rendererMock;
    }

    public function testGetStore()
    {
        $this->assertEquals($this->storeMock, $this->model->getStore());
    }

    public function testSetStore()
    {
        $this->model->setStore($this->storeMock);
        $this->assertEquals($this->storeMock, $this->model->getStore());
    }

    public function testGetFormats()
    {
        $this->storeMock->expects($this->once())->method('getId');

        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn('someValue');

        $rendererMock = $this->createMock(DataObject::class);

        $this->addressHelperMock->expects(
            $this->any()
        )->method(
            'getRenderer'
        )->willReturn(
            $rendererMock
        );

        $firstExpected = new DataObject();
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

        $secondExpected = new DataObject();
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
        $expectedResult = [$firstExpected, $secondExpected];

        $this->assertEquals($expectedResult, $this->model->getFormats());
    }

    public function testGetFormatsUsesXmlPathConstantForScopeConfig(): void
    {
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);

        $capturedPaths = [];
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnCallback(function (string $path) use (&$capturedPaths): string {
                $capturedPaths[] = $path;
                return 'formatValue';
            });

        $this->addressHelperMock->expects($this->exactly(2))
            ->method('getRenderer')
            ->with(DefaultRenderer::class)
            ->willReturn($this->createRendererMock());

        $this->model->getFormats();

        $this->assertSame(
            [
                Config::XML_PATH_ADDRESS_TEMPLATE . 'format_one',
                Config::XML_PATH_ADDRESS_TEMPLATE . 'format_two',
            ],
            $capturedPaths
        );
    }

    public function testGetFormatsUsesSubclassXmlPathConstantForScopeConfig(): void
    {
        $model = $this->createModel(ConfigTesting::class);
        $model->setStore($this->storeMock);

        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);

        $capturedPaths = [];
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnCallback(function (string $path) use (&$capturedPaths): string {
                $capturedPaths[] = $path;
                return 'formatValue';
            });

        $this->addressHelperMock->expects($this->exactly(2))
            ->method('getRenderer')
            ->with(ConfigTesting::DEFAULT_ADDRESS_RENDERER)
            ->willReturn($this->createRendererMock());

        $model->getFormats();

        $this->assertSame(
            [
                ConfigTesting::XML_PATH_ADDRESS_TEMPLATE . 'format_one',
                ConfigTesting::XML_PATH_ADDRESS_TEMPLATE . 'format_two',
            ],
            $capturedPaths
        );
    }

    public function testGetFormatByCodeUsesSubclassDefaultRenderer(): void
    {
        $model = $this->createModel(ConfigTesting::class);
        $model->setStore($this->storeMock);

        $this->storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);

        $this->scopeConfigMock->method('getValue')->willReturn('formatValue');

        $this->addressHelperMock->expects($this->atLeastOnce())
            ->method('getRenderer')
            ->with(ConfigTesting::DEFAULT_ADDRESS_RENDERER)
            ->willReturn($this->createRendererMock());

        $format = $model->getFormatByCode('unknown_format_code');

        $this->assertSame('default', $format->getCode());
    }

    public function testGetFormatsPassesStoreScopeToScopeConfig(): void
    {
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);

        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->with(
                $this->logicalOr(
                    Config::XML_PATH_ADDRESS_TEMPLATE . 'format_one',
                    Config::XML_PATH_ADDRESS_TEMPLATE . 'format_two'
                ),
                ScopeInterface::SCOPE_STORE,
                $this->storeMock
            )
            ->willReturn('formatValue');

        $this->addressHelperMock->method('getRenderer')->willReturn($this->createRendererMock());

        $this->model->getFormats();
    }
}
