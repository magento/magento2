<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\PageCache;

class PageCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testIdentifierProperty()
    {
        $identifier = 'page_cache';

        $poolMock = $this->getMockBuilder('\Magento\Framework\App\Cache\Frontend\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $poolMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $this->equalTo($identifier)
        )->will(
            $this->returnArgument(0)
        );
        $model = new \Magento\Framework\App\PageCache\Cache($poolMock);
        $this->assertInstanceOf('Magento\Framework\App\PageCache\Cache', $model);
    }
}
