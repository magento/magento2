<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\FlushCacheByTags;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * Unit tests for the \Magento\Framework\App\Cache\FlushCacheByTags class.
 */
class FlushCacheByTagsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Type\FrontendPool
     */
    private $frontendPool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Tag\Resolver
     */
    private $tagResolver;

    /**
     * @var \Magento\Framework\App\Cache\FlushCacheByTags
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->cacheState = $this->getMockForAbstractClass(StateInterface::class);
        $this->frontendPool = $this->createMock(FrontendPool::class);
        $this->tagResolver = $this->createMock(Resolver::class);

        $this->plugin = new FlushCacheByTags(
            $this->frontendPool,
            $this->cacheState,
            ['test'],
            $this->tagResolver
        );
    }

    /**
     * @return void
     */
    public function testAroundSave(): void
    {
        $resource = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tagResolver->expects($this->atLeastOnce())->method('getTags')->with($model)->willReturn([]);

        $result = $this->plugin->aroundSave(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );

        $this->assertSame($resource, $result);
    }

    /**
     * @return void
     */
    public function testAroundDelete(): void
    {
        $resource = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tagResolver->expects($this->atLeastOnce())->method('getTags')->with($model)->willReturn([]);

        $result = $this->plugin->aroundDelete(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );

        $this->assertSame($resource, $result);
    }
}
