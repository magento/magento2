<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class ScopePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Scope\Reader|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_reader;

    /**
     * @var \Magento\Framework\App\Config\Scope\ReaderPoolInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerPool;

    /**
     * @var \Magento\Framework\App\Config\DataFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataFactory;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    protected $_object;

    public function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_readerPool = $this->getMockForAbstractClass('\Magento\Framework\App\Config\Scope\ReaderPoolInterface');
        $this->_reader = $this->getMockForAbstractClass('\Magento\Framework\App\Config\Scope\ReaderInterface');
        $this->_dataFactory = $this->getMockBuilder(
            '\Magento\Framework\App\Config\DataFactory'
        )->disableOriginalConstructor()->getMock();
        $this->_cache = $this->getMock('\Magento\Framework\Cache\FrontendInterface');
        $this->_object = $helper->getObject(
            '\Magento\Framework\App\Config\ScopePool',
            [
                'readerPool' => $this->_readerPool,
                'dataFactory' => $this->_dataFactory,
                'cache' => $this->_cache,
                'cacheId' => 'test_cache_id'
            ]
        );
    }

    /**
     * @dataProvider getScopeDataProvider
     *
     * @param string $scopeType
     * @param string $scope
     * @param array $data
     * @param string|null $cachedData
     */
    public function testGetScope($scopeType, $scope, array $data, $cachedData)
    {
        $scopeCode = $scope instanceof \Magento\Framework\App\ScopeInterface ? $scope->getCode() : $scope;
        $cacheKey = "test_cache_id|{$scopeType}|{$scopeCode}";

        $this->_readerPool->expects(
            $this->any()
        )->method(
            'getReader'
        )->with(
            $scopeType
        )->will(
            $this->returnValue($this->_reader)
        );
        $this->_cache->expects($this->once())->method('load')->with($cacheKey)->will($this->returnValue($cachedData));

        if (!$cachedData) {
            $this->_reader->expects($this->once())->method('read')->with('testScope')->will($this->returnValue($data));
            $this->_cache->expects(
                $this->once()
            )->method(
                'save'
            )->with(
                serialize($data),
                $cacheKey,
                [\Magento\Framework\App\Config\ScopePool::CACHE_TAG]
            );
        }

        $configData = $this->getMockBuilder('\Magento\Framework\App\Config\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_dataFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ['data' => $data]
        )->will(
            $this->returnValue($configData)
        );
        $this->assertInstanceOf(
            '\Magento\Framework\App\Config\DataInterface',
            $this->_object->getScope($scopeType, $scope)
        );

        // second call to check caching
        $this->assertInstanceOf(
            '\Magento\Framework\App\Config\DataInterface',
            $this->_object->getScope($scopeType, $scope)
        );
    }

    public function getScopeDataProvider()
    {
        $baseScope = $this->getMockForAbstractClass('Magento\Framework\App\ScopeInterface');
        $baseScope->expects($this->any())->method('getCode')->will($this->returnValue('testScope'));
        return [
            ['scopeType1', 'testScope', ['key' => 'value'], null],
            ['scopeType2', 'testScope', ['key' => 'value'], serialize(['key' => 'value'])],
            ['scopeType1', $baseScope, ['key' => 'value'], null]
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
