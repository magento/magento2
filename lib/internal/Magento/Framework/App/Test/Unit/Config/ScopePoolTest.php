<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\Scope\ReaderInterface;
use Magento\Framework\App\Config\Scope\ReaderPoolInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScopePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_reader;

    /**
     * @var ReaderPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_readerPool;

    /**
     * @var \Magento\Framework\App\Config\DataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_dataFactory;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    private $_object;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_readerPool = $this->getMockForAbstractClass(ReaderPoolInterface::class);
        $this->_reader = $this->getMockForAbstractClass(ReaderInterface::class);
        $this->_dataFactory = $this->getMockBuilder(
            \Magento\Framework\App\Config\DataFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->_cache = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->_object = $objectManager->getObject(
            \Magento\Framework\App\Config\ScopePool::class,
            [
                'readerPool' => $this->_readerPool,
                'dataFactory' => $this->_dataFactory,
                'cache' => $this->_cache,
                'cacheId' => 'test_cache_id'
            ]
        );
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $objectManager->setBackwardCompatibleProperty($this->_object, 'serializer', $this->serializerMock);

        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getBasePath',
                    'getModuleName',
                    'setModuleName',
                    'getActionName',
                    'setActionName',
                    'getParam',
                    'getParams',
                    'setParams',
                    'getCookie',
                    'isSecure',
                    'getServer',
                    'getHttpHost'
                ]
            )->getMock();
        $reflection = new \ReflectionClass(get_class($this->_object));
        $reflectionProperty = $reflection->getProperty('request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_object, $requestMock);
        $requestMock->expects($this->any())
            ->method('getBasePath')
            ->willReturn('baseUrl');
    }

    /**
     * @param string $scopeType
     * @param string $scope
     * @param array $data
     * @dataProvider getScopeConfigNotCachedProvider
     */
    public function testGetScopeConfigNotCached($scopeType, $scope, array $data)
    {
        $scopeCode = $scope instanceof \Magento\Framework\App\ScopeInterface ? $scope->getCode() : $scope;
        $cacheKey = "test_cache_id|{$scopeType}|{$scopeCode}|baseUrl";
        $this->_readerPool->expects($this->any())
            ->method('getReader')
            ->with($scopeType)
            ->willReturn($this->_reader);
        $this->_cache->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn(null);
        $this->_reader->expects($this->once())
            ->method('read')
            ->with('testScope')
            ->willReturn($data);
        $serializedData = 'serialized data';
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->_cache->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                $cacheKey,
                [\Magento\Framework\App\Config\ScopePool::CACHE_TAG]
            );
        $configData = $this->getMock(\Magento\Framework\App\Config\Data::class, [], [], '', false);
        $this->_dataFactory->expects($this->once())
            ->method('create')
            ->with(['data' => $data])
            ->willReturn($configData);
        $this->assertInstanceOf(
            \Magento\Framework\App\Config\DataInterface::class,
            $this->_object->getScope($scopeType, $scope)
        );
        $this->assertInstanceOf(
            \Magento\Framework\App\Config\DataInterface::class,
            $this->_object->getScope($scopeType, $scope)
        );
    }

    public function getScopeConfigNotCachedProvider()
    {
        $baseScope = $this->getMockForAbstractClass(\Magento\Framework\App\ScopeInterface::class);
        $baseScope->expects($this->any())->method('getCode')->will($this->returnValue('testScope'));
        return [
            ['scopeType1', 'testScope', ['key' => 'value'], null],
            ['scopeType1', $baseScope, ['key' => 'value'], null]
        ];
    }

    /**
     * @param string $scopeType
     * @param string $scope
     * @param array $data
     * @param string $cachedData
     * @dataProvider getScopeConfigCachedProvider
     */
    public function testGetScopeConfigCached($scopeType, $scope, array $data, $cachedData)
    {
        $scopeCode = $scope instanceof \Magento\Framework\App\ScopeInterface ? $scope->getCode() : $scope;
        $cacheKey = "test_cache_id|{$scopeType}|{$scopeCode}|baseUrl";
        $this->_readerPool->expects($this->any())
            ->method('getReader')
            ->with($scopeType)
            ->willReturn($this->_reader);
        $this->_cache->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn($cachedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $configData = $this->getMock(\Magento\Framework\App\Config\Data::class, [], [], '', false);
        $this->_dataFactory->expects($this->once())
            ->method('create')
            ->with(['data' => $data])
            ->willReturn($configData);
        $this->assertInstanceOf(
            \Magento\Framework\App\Config\DataInterface::class,
            $this->_object->getScope($scopeType, $scope)
        );
        $this->assertInstanceOf(
            \Magento\Framework\App\Config\DataInterface::class,
            $this->_object->getScope($scopeType, $scope)
        );
    }

    public function getScopeConfigCachedProvider()
    {
        return [
            ['scopeType2', 'testScope', ['key' => 'value'], '{"key":"value"}'],
        ];
    }

    public function testClean()
    {
        $this->_cache->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            [\Magento\Framework\App\Config\ScopePool::CACHE_TAG]
        );
        $this->_object->clean('testScope');
    }
}
