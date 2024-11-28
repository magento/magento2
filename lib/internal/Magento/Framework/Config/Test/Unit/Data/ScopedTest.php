<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Data;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data\Scoped;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->_readerMock = $this->getMockForAbstractClass(ReaderInterface::class);
        $this->_configScopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->_cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->_model = $this->objectManager->getObject(
            Scoped::class,
            [
                'reader' => $this->_readerMock,
                'configScope' => $this->_configScopeMock,
                'cache' => $this->_cacheMock,
                'cacheId' => 'tag',
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * @param string $path
     * @param mixed $expectedValue
     * @param string $default
     * @dataProvider getConfigByPathDataProvider
     */
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
}
