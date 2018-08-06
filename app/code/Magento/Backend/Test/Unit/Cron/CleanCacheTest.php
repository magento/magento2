<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Cron;

class CleanCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testCleanCache()
    {
        $cacheBackendMock = $this->getMockForAbstractClass(\Zend_Cache_Backend_Interface::class);
        $cacheFrontendMock = $this->getMockForAbstractClass(\Magento\Framework\Cache\FrontendInterface::class);
        $frontendPoolMock = $this->createMock(\Magento\Framework\App\Cache\Frontend\Pool::class);

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
        )->will(
            $this->returnValue($cacheBackendMock)
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
        )->will(
            $this->returnValue($cacheFrontendMock)
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /**
         * @var \Magento\Backend\Cron\CleanCache
         */
        $model = $objectManagerHelper->getObject(
            \Magento\Backend\Cron\CleanCache::class,
            [
                'cacheFrontendPool' => $frontendPoolMock,
            ]
        );

        $model->execute();
    }
}
