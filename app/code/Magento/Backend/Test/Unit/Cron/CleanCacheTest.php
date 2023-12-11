<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Cron;

use Magento\Backend\Cron\CleanCache;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CleanCacheTest extends TestCase
{
    public function testCleanCache()
    {
        $cacheBackendMock = $this->getMockForAbstractClass(\Zend_Cache_Backend_Interface::class);
        $cacheFrontendMock = $this->getMockForAbstractClass(FrontendInterface::class);
        $frontendPoolMock = $this->createMock(Pool::class);

        $cacheBackendMock->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_OLD,
            []
        );

        $cacheFrontendMock->expects(
            $this->once()
        )->method(
            'getBackend'
        )->willReturn(
            $cacheBackendMock
        );

        $frontendPoolMock->expects(
            $this->any()
        )->method(
            'valid'
        )->will(
            $this->onConsecutiveCalls(true, false)
        );

        $frontendPoolMock->expects(
            $this->any()
        )->method(
            'current'
        )->willReturn(
            $cacheFrontendMock
        );

        $objectManagerHelper = new ObjectManager($this);
        /**
         * @var CleanCache
         */
        $model = $objectManagerHelper->getObject(
            CleanCache::class,
            [
                'cacheFrontendPool' => $frontendPoolMock,
            ]
        );

        $model->execute();
    }
}
