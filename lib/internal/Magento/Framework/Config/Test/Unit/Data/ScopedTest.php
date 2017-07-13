<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Data;

class ScopedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Config\Data\Scoped
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_readerMock = $this->createMock(\Magento\Framework\Config\ReaderInterface::class);
        $this->_configScopeMock = $this->createMock(\Magento\Framework\Config\ScopeInterface::class);
        $this->_cacheMock = $this->createMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);

        $this->_model = $this->objectManager->getObject(
            \Magento\Framework\Config\Data\Scoped::class,
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

    public function getConfigByPathDataProvider()
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
        )->will(
            $this->returnValue('adminhtml')
        );

        /** set empty cache data */
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'adminhtml::tag'
        )->will(
            $this->returnValue(false)
        );

        /** get data from reader  */
        $this->_readerMock->expects(
            $this->once()
        )->method(
            'read'
        )->with(
            'adminhtml'
        )->will(
            $this->returnValue($testValue)
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
        )->will(
            $this->returnValue('adminhtml')
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
