<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Marketplace\Test\Unit\Helper;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Marketplace\Helper\Cache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var  SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var Cache
     */
    private $cacheHelper;

    protected function setUp(): void
    {
        $this->cache = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $this->cacheHelper = $objectManagerHelper->getObject(
            Cache::class,
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

        $this->assertFalse($this->cacheHelper->loadPartnersFromCache());
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
