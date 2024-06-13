<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Cron;

use Magento\Backend\Cron\CleanCache;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Zend_Cache_Backend_Interface;

class CleanCacheTest extends TestCase
{
    public function testCleanCache()
    {
        $cacheBackendMock = $this->createMock(Zend_Cache_Backend_Interface::class);
        $cacheFrontendMock = $this->createMock(FrontendInterface::class);
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

        $callCount = 0;
        $frontendPoolMock->expects(
            $this->any()
        )->method(
            'valid'
        )->willReturnCallback(function () use (&$callCount) {
            $callCount++;
            return $callCount === 1; // true on first call, false on second
        });

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
