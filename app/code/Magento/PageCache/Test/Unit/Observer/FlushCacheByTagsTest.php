<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\PageCache\Test\Unit\Observer;

class FlushCacheByTagsTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\PageCache\Observer\FlushCacheByTags */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\PageCache\Cache */
    protected $_cacheMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Cache\Type */
    private $fullPageCacheMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Tag\Resolver */
    private $tagResolver;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_configMock = $this->createPartialMock(\Magento\PageCache\Model\Config::class, ['getType', 'isEnabled']);
        $this->_cacheMock = $this->createPartialMock(\Magento\Framework\App\PageCache\Cache::class, ['clean']);
        $this->fullPageCacheMock = $this->createPartialMock(\Magento\PageCache\Model\Cache\Type::class, ['clean']);

        $this->_model = new \Magento\PageCache\Observer\FlushCacheByTags(
            $this->_configMock,
            $this->_cacheMock
        );

        $this->tagResolver = $this->createMock(\Magento\Framework\App\Cache\Tag\Resolver::class);

        $helper->setBackwardCompatibleProperty($this->_model, 'tagResolver', $this->tagResolver);
        $reflection = new \ReflectionClass(\Magento\PageCache\Observer\FlushCacheByTags::class);
        $reflectionProperty = $reflection->getProperty('fullPageCache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_model, $this->fullPageCacheMock);
    }

    /**
     * Test case for cache invalidation
     *
     * @dataProvider flushCacheByTagsDataProvider
     * @param $cacheState
     */
    public function testExecute($cacheState)
    {
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));
        $observerObject = $this->createMock(\Magento\Framework\Event\Observer::class);
        $observedObject = $this->createMock(\Magento\Store\Model\Store::class);

        if ($cacheState) {
            $tags = ['cache_1', 'cache_group'];
            $expectedTags = ['cache_1', 'cache_group'];

            $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getObject']);
            $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($observedObject));
            $observerObject->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
            $this->_configMock->expects($this->once())
                ->method('getType')
                ->willReturn(\Magento\PageCache\Model\Config::BUILT_IN);
            $this->tagResolver->expects($this->once())->method('getTags')->will($this->returnValue($tags));

            $this->fullPageCacheMock->expects($this->once())
                ->method('clean')
                ->with(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $this->equalTo($expectedTags));
        }

        $result = $this->_model->execute($observerObject);
        $this->assertNull($result);
    }

    public function flushCacheByTagsDataProvider()
    {
        return [
            'full_page cache type is enabled' => [true],
            'full_page cache type is disabled' => [false]
        ];
    }

    public function testExecuteWithEmptyTags()
    {
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $observerObject = $this->createMock(\Magento\Framework\Event\Observer::class);
        $observedObject = $this->createMock(\Magento\Store\Model\Store::class);

        $tags = [];

        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getObject']);
        $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($observedObject));
        $observerObject->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN)
        );
        $this->tagResolver->expects($this->once())->method('getTags')->will($this->returnValue($tags));

        $this->fullPageCacheMock->expects($this->never())->method('clean');

        $this->_model->execute($observerObject);
    }
}
