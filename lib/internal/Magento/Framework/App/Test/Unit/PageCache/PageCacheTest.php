<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\PageCache;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\PageCache\Cache;
use PHPUnit\Framework\TestCase;

class PageCacheTest extends TestCase
{
    public function testIdentifierProperty()
    {
        $identifier = 'page_cache';

        $poolMock = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $poolMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $identifier
        )->willReturnArgument(
            0
        );
        $model = new Cache($poolMock);
        $this->assertInstanceOf(Cache::class, $model);
    }
}
