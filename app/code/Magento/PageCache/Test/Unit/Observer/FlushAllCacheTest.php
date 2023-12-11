<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Observer\FlushAllCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\PageCache\Observer\FlushAllCache
 */
class FlushAllCacheTest extends TestCase
{
    /**
     * @var FlushAllCache
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Type|MockObject
     */
    private $fullPageCacheMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createPartialMock(Config::class, ['getType', 'isEnabled']);
        $this->fullPageCacheMock = $this->createPartialMock(Type::class, ['clean']);
        $this->observerMock = $this->createMock(Observer::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            FlushAllCache::class,
            [
                'config' => $this->configMock,
                'fullPageCache' => $this->fullPageCacheMock
            ]
        );
    }

    /**
     * Test case for flushing all the cache
     */
    public function testExecute()
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->willReturn(
            Config::BUILT_IN
        );

        $this->fullPageCacheMock->expects($this->once())->method('clean');
        $this->model->execute($this->observerMock);
    }

    /**
     * Test case for flushing all the cache with varnish enabled
     */
    public function testExecuteWithVarnish()
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->willReturn(
            Config::VARNISH
        );

        $this->fullPageCacheMock->expects($this->never())->method('clean');
        $this->model->execute($this->observerMock);
    }
}
