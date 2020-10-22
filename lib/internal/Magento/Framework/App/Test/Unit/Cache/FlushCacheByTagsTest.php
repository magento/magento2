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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \Magento\Framework\App\Cache\FlushCacheByTags class.
 */
class FlushCacheByTagsTest extends TestCase
{
    /**
     * @var MockObject|StateInterface
     */
    private $cacheState;

    /**
     * @var MockObject|FrontendPool
     */
    private $frontendPool;

    /**
     * @var MockObject|Resolver
     */
    private $tagResolver;

    /**
     * @var FlushCacheByTags
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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
    public function testAfterSave(): void
    {
        $resource = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tagResolver->expects($this->atLeastOnce())->method('getTags')->with($model)->willReturn([]);

        $result = $this->plugin->afterSave(
            $resource,
            $resource,
            $model
        );

        $this->assertSame($resource, $result);
    }

    /**
     * @return void
     */
    public function testAfterDelete(): void
    {
        $resource = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tagResolver->expects($this->atLeastOnce())->method('getTags')->with($model)->willReturn([]);

        $result = $this->plugin->afterDelete(
            $resource,
            $resource,
            $model
        );

        $this->assertSame($resource, $result);
    }
}
