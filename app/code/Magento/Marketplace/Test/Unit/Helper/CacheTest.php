<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Helper;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Helper\Cache
     */
    private $cacheHelperMock;

    protected function setUp()
    {
        $this->cacheHelperMock = $this->getCacheHelperMock(['getCache']);
    }

    /**
     * @covers \Magento\Marketplace\Helper\Cache::loadPartnersFromCache
     */
    public function testLoadPartnersFromCache()
    {
        $cache = $this->getCacheMock();
        $this->cacheHelperMock
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));
        $cache->expects($this->once())
            ->method('load')
            ->will($this->returnValue(''));

        $this->cacheHelperMock->loadPartnersFromCache();
    }

    /**
     * @covers \Magento\Marketplace\Helper\Cache::savePartnersToCache
     */
    public function testSavePartnersToCache()
    {
        $cache = $this->getCacheMock();
        $this->cacheHelperMock
            ->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cache));
        $cache->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $this->cacheHelperMock->savePartnersToCache([]);
    }

    /**
     * Gets cache helper mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Helper\Cache
     */
    public function getCacheHelperMock($methods = null)
    {
        return $this->getMock('Magento\Marketplace\Helper\Cache', $methods, [], '', false);
    }

    /**
     * Gets Filesystem mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Config\CacheInterface
     */
    public function getCacheMock()
    {
        return $this->getMockForAbstractClass('Magento\Framework\Config\CacheInterface');
    }
}
