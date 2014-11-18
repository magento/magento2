<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Plugin\Model\Resource;

use Magento\TestFramework\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Plugin\Model\Resource\Config */
    protected $config;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheState;

    /** @var \Magento\Catalog\Model\Resource\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $subject;

    protected function setUp()
    {
        $this->cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $this->cacheState = $this->getMock('Magento\Framework\App\Cache\StateInterface');
        $this->subject = $this->getMock('Magento\Catalog\Model\Resource\Config', [], [], '', false);
    }

    public function testGetAttributesUsedInListingOnCacheDisabled()
    {
        $this->cache->expects($this->never())->method('load');

        $this->assertEquals(
            ['attributes'],
            $this->getConfig(false)->aroundGetAttributesUsedInListing(
                $this->subject,
                $this->mockPluginProceed(['attributes'])
            )
        );
    }

    public function testGetAttributesUsedInListingFromCache()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\Resource\Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID . $entityTypeId
            . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(serialize($attributes));

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedInListing(
                $this->subject,
                $this->mockPluginProceed()
            )
        );
    }

    public function testGetAttributesUsedInListingWithCacheSave()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\Resource\Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID . $entityTypeId
            . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->cache->expects($this->any())->method('save')->with(
            serialize($attributes),
            $cacheId,
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedInListing(
                $this->subject,
                $this->mockPluginProceed($attributes)
            )
        );
    }

    public function testGetAttributesUsedForSortByOnCacheDisabled()
    {
        $this->cache->expects($this->never())->method('load');

        $this->assertEquals(
            ['attributes'],
            $this->getConfig(false)->aroundGetAttributesUsedForSortBy(
                $this->subject,
                $this->mockPluginProceed(['attributes'])
            )
        );
    }

    public function testGetAttributesUsedForSortByFromCache()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\Resource\Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(serialize($attributes));

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedForSortBy(
                $this->subject,
                $this->mockPluginProceed()
            )
        );
    }

    public function testGetAttributesUsedForSortByWithCacheSave()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\Resource\Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->cache->expects($this->any())->method('save')->with(
            serialize($attributes),
            $cacheId,
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedForSortBy(
                $this->subject,
                $this->mockPluginProceed($attributes)
            )
        );
    }

    /**
     * @param bool $cacheEnabledFlag
     * @return \Magento\Catalog\Plugin\Model\Resource\Config
     */
    protected function getConfig($cacheEnabledFlag)
    {
        $this->cacheState->expects($this->any())->method('isEnabled')
            ->with(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER)->willReturn($cacheEnabledFlag);
        return (new ObjectManager($this))->getObject(
            'Magento\Catalog\Plugin\Model\Resource\Config',
            [
                'cache' => $this->cache,
                'cacheState' => $this->cacheState
            ]
        );
    }

    /**
     * @param mixed $returnValue
     * @return callable
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }
}
