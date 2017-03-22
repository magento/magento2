<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Helper;

use Magento\Framework\Serialize\SerializerInterface;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var  SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Marketplace\Helper\Cache
     */
    private $cacheHelper;

    protected function setUp()
    {
        $this->cache = $this->getMockForAbstractClass(\Magento\Framework\Config\CacheInterface::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheHelper = $objectManagerHelper->getObject(
            \Magento\Marketplace\Helper\Cache::class,
            [
                'cache' => $this->cache,
                'serializer' => $this->serializer,
            ]
        );
    }

    public function testLoadPartnersFromCache()
    {
        $partners = ['partner1', 'partner2'];
        $serializedPartners = '["partner1", "partner2"]';
        $this->cache->expects($this->once())
            ->method('load')
            ->with('partners')
            ->willReturn($serializedPartners);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedPartners)
            ->willReturn($partners);

        $this->assertSame($partners, $this->cacheHelper->loadPartnersFromCache());
    }

    public function testLoadPartnersFromCacheNoCachedData()
    {
        $this->cache->expects($this->once())
            ->method('load')
            ->with('partners')
            ->willReturn(false);
        $this->serializer->expects($this->never())
            ->method('unserialize');

        $this->assertSame(false, $this->cacheHelper->loadPartnersFromCache());
    }

    public function testSavePartnersToCache()
    {
        $partners = ['partner1', 'partner2'];
        $serializedPartners = '["partner1", "partner2"]';
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($partners)
            ->willReturn($serializedPartners);
        $this->cache->expects($this->once())
            ->method('save')
            ->with($serializedPartners);

        $this->cacheHelper->savePartnersToCache($partners);
    }
}
