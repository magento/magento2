<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Data;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data\Scoped;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ScopedTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Scoped
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_readerMock;

    /**
     * @var MockObject
     */
    protected $_configScopeMock;

    /**
     * @var MockObject
     */
    protected $_cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var ConfigWriterInterface|MockObject
     */
    private $configWriterMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->_readerMock = $this->createMock(ReaderInterface::class);
        $this->_configScopeMock = $this->createMock(ScopeInterface::class);
        $this->_cacheMock = $this->createMock(CacheInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->configWriterMock = $this->createMock(ConfigWriterInterface::class);

        $this->_model = $this->objectManager->getObject(
            Scoped::class,
            [
                'reader' => $this->_readerMock,
                'configScope' => $this->_configScopeMock,
                'cache' => $this->_cacheMock,
                'cacheId' => 'tag',
                'serializer' => $this->serializerMock,
                'configWriter' => null
            ]
        );
    }

    /**
     * @param string $path
     * @param mixed $expectedValue
     * @param string $default     */
    #[DataProvider('getConfigByPathDataProvider')]
    public function testGetConfigByPath($path, $expectedValue, $default)
    {
        $testData = [
            'key_1' => [
                'key_1.1' => ['key_1.1.1' => 'value_1.1.1'],
                'key_1.2' => ['some' => 'arrayValue'],
            ],
        ];
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->_readerMock->expects($this->once())
            ->method('read')
            ->willReturn([]);
        $this->_model->merge($testData);
        $this->assertEquals($expectedValue, $this->_model->get($path, $default));
    }

    /**
     * @return array
     */
    public static function getConfigByPathDataProvider()
    {
        return [
            ['key_1/key_1.1/key_1.1.1', 'value_1.1.1', 'error'],
            ['key_1/key_1.2', ['some' => 'arrayValue'], 'error'],
            [
                'key_1',
                ['key_1.1' => ['key_1.1.1' => 'value_1.1.1'], 'key_1.2' => ['some' => 'arrayValue']],
                'error'
            ],
            ['key_1/notExistedKey', 'defaultValue', 'defaultValue']
        ];
    }

    public function testGetScopeSwitchingWithNonCachedData()
    {
        $testValue = ['some' => 'testValue'];
        $serializedData = 'serialized data';

        /** change current area */
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'getCurrentScope'
        )->willReturn(
            'adminhtml'
        );

        /** set empty cache data */
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'adminhtml::tag'
        )->willReturn(
            false
        );

        /** get data from reader  */
        $this->_readerMock->expects(
            $this->once()
        )->method(
            'read'
        )->with(
            'adminhtml'
        )->willReturn(
            $testValue
        );

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($testValue)
            ->willReturn($serializedData);

        /** test cache saving  */
        $this->_cacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData, 'adminhtml::tag');

        /** test config value existence */
        $this->assertEquals('testValue', $this->_model->get('some'));

        /** test preventing of double config data loading from reader */
        $this->assertEquals('testValue', $this->_model->get('some'));
    }

    public function testGetScopeSwitchingWithCachedData()
    {
        $testValue = ['some' => 'testValue'];
        $serializedData = 'serialized data';

        /** change current area */
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'getCurrentScope'
        )->willReturn(
            'adminhtml'
        );

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($testValue);

        /** set cache data */
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('adminhtml::tag')
            ->willReturn($serializedData);

        /** test preventing of getting data from reader  */
        $this->_readerMock->expects($this->never())->method('read');

        /** test preventing of cache saving  */
        $this->_cacheMock->expects($this->never())->method('save');

        /** test config value existence */
        $this->assertEquals('testValue', $this->_model->get('some'));

        /** test preventing of double config data loading from reader */
        $this->assertEquals('testValue', $this->_model->get('some'));
    }

    /**
     * When a compiled file exists for a scope, data should be loaded from it.
     * Cache backend and XML reader must NOT be called.
     */
    public function testGetScopeSwitchingWithCompiledData(): void
    {
        $compiledData = ['compiled' => 'scopeValue'];

        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('adminhtml');

        $this->_cacheMock->expects($this->never())
            ->method('load');
        $this->_readerMock->expects($this->never())
            ->method('read');
        $this->serializerMock->expects($this->never())
            ->method('unserialize');

        $model = $this->createCompiledScopedConfig(
            compiledScopes: ['adminhtml::tag' => $compiledData],
            configWriter: $this->configWriterMock
        );

        $this->assertEquals('scopeValue', $model->get('compiled'));

        /** Verify repeated access does not trigger additional loads */
        $this->assertEquals('scopeValue', $model->get('compiled'));
    }

    /**
     * On scope cache miss with configWriter present, the compiled file should be written after reading.
     */
    public function testCompiledFileWrittenOnScopeCacheMiss(): void
    {
        $testValue = ['some' => 'testValue'];
        $serializedData = 'serialized data';

        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('adminhtml');

        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('adminhtml::tag')
            ->willReturn(false);

        $this->_readerMock->expects($this->once())
            ->method('read')
            ->with('adminhtml')
            ->willReturn($testValue);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($testValue)
            ->willReturn($serializedData);

        $this->_cacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData, 'adminhtml::tag');

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with('adminhtml::tag', $testValue);

        $model = $this->createCompiledScopedConfig(
            compiledScopes: [],
            configWriter: $this->configWriterMock
        );

        $this->assertEquals('testValue', $model->get('some'));
    }

    /**
     * The 'primary' scope should never attempt compilation (it bypasses cache already).
     */
    public function testPrimaryScopeNotCompiled(): void
    {
        $primaryData = ['primary' => 'data'];

        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('primary');

        $this->_cacheMock->expects($this->never())
            ->method('load');

        $this->_readerMock->expects($this->once())
            ->method('read')
            ->with('primary')
            ->willReturn($primaryData);

        $this->configWriterMock->expects($this->never())
            ->method('write');

        $model = $this->createCompiledScopedConfig(
            compiledScopes: [],
            configWriter: $this->configWriterMock
        );

        $this->assertEquals('data', $model->get('primary'));
    }

    /**
     * When configWriter is null, behavior is identical to original (backward compat).
     */
    public function testNoCompilationWithoutConfigWriter(): void
    {
        $testValue = ['some' => 'testValue'];
        $serializedData = 'serialized data';

        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('frontend');

        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('frontend::tag')
            ->willReturn(false);

        $this->_readerMock->expects($this->once())
            ->method('read')
            ->with('frontend')
            ->willReturn($testValue);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($testValue)
            ->willReturn($serializedData);

        $this->_cacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData, 'frontend::tag');

        /** Use the default model (no configWriter) */
        $model = $this->objectManager->getObject(
            Scoped::class,
            [
                'reader' => $this->_readerMock,
                'configScope' => $this->_configScopeMock,
                'cache' => $this->_cacheMock,
                'cacheId' => 'tag',
                'serializer' => $this->serializerMock,
                'configWriter' => null
            ]
        );

        $this->assertEquals('testValue', $model->get('some'));
    }

    /**
     * Create a Scoped instance with compiled-file behavior controlled by test parameters.
     *
     * Uses an anonymous subclass to override filesystem checks for compiled configs,
     * since file_exists() and include() cannot be mocked in unit tests.
     *
     * @param array<string, array> $compiledScopes Map of scope cache key => compiled data
     * @param ConfigWriterInterface|null $configWriter
     * @return Scoped
     */
    private function createCompiledScopedConfig(
        array $compiledScopes = [],
        ?ConfigWriterInterface $configWriter = null
    ): Scoped {
        return new TestableCompiledScoped(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheMock,
            'tag',
            $this->serializerMock,
            $compiledScopes,
            $configWriter
        );
    }
}
