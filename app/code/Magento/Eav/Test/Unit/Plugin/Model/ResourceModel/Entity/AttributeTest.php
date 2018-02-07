<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Test\Unit\Plugin\Model\ResourceModel\Entity;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheState;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $subject;

    protected function setUp()
    {
        $this->cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $this->cacheState = $this->getMock('Magento\Framework\App\Cache\StateInterface');
        $this->subject = $this->getMock('Magento\Eav\Model\ResourceModel\Entity\Attribute', [], [], '', false);
    }

    public function testGetStoreLabelsByAttributeIdOnCacheDisabled()
    {
        $this->cache->expects($this->never())->method('load');

        $this->assertEquals(
            'attributeId',
            $this->getAttribute(false)->aroundGetStoreLabelsByAttributeId(
                $this->subject,
                $this->mockPluginProceed('attributeId'),
               'attributeId'
            )
        );
    }

    public function testGetStoreLabelsByAttributeIdFromCache()
    {
        $attributeId = 1;
        $attributes = ['k' => 'v'];
        $cacheId = \Magento\Eav\Plugin\Model\ResourceModel\Entity\Attribute::STORE_LABEL_ATTRIBUTE . $attributeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(serialize($attributes));

        $this->assertEquals(
            $attributes,
            $this->getAttribute(true)->aroundGetStoreLabelsByAttributeId(
                $this->subject,
                $this->mockPluginProceed(),
                $attributeId
            )
        );
    }

    public function testGetStoreLabelsByAttributeIdWithCacheSave()
    {
        $attributeId = 1;
        $cacheId = \Magento\Eav\Plugin\Model\ResourceModel\Entity\Attribute::STORE_LABEL_ATTRIBUTE . $attributeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->cache->expects($this->any())->method('save')->with(
            serialize([$attributeId]),
            $cacheId,
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            [$attributeId],
            $this->getAttribute(true)->aroundGetStoreLabelsByAttributeId(
                $this->subject,
                $this->mockPluginProceed([$attributeId]),
                $attributeId
            )
        );
    }

    /**
     * @param bool $cacheEnabledFlag
     * @return \Magento\Eav\Plugin\Model\ResourceModel\Entity\Attribute
     */
    protected function getAttribute($cacheEnabledFlag)
    {
        $this->cacheState->expects($this->any())->method('isEnabled')
            ->with(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER)->willReturn($cacheEnabledFlag);
        return (new ObjectManager($this))->getObject(
            'Magento\Eav\Plugin\Model\ResourceModel\Entity\Attribute',
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
