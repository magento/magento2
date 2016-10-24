<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache;

class FlushCacheByTagsTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->cacheState = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\StateInterface::class);
        $this->frontendPool = $this->getMock(\Magento\Framework\App\Cache\Type\FrontendPool::class, [], [], '', false);
        $this->tagResolver = $this->getMock(\Magento\Framework\App\Cache\Tag\Resolver::class, [], [], '', false);

        $this->plugin = new \Magento\Framework\App\Cache\FlushCacheByTags(
            $this->frontendPool,
            $this->cacheState,
            ['test'],
            $this->tagResolver
        );
    }

    public function testAroundSave()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->plugin->aroundSave(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );
        $this->assertSame($resource, $result);
    }

    public function testAroundDelete()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->plugin->aroundDelete(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );
        $this->assertSame($resource, $result);
    }

    public function testAroundSaveWithInterface()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)

            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->plugin->aroundSave(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );
        $this->assertSame($resource, $result);
    }
}
