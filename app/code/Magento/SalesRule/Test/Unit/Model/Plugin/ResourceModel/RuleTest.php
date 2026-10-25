<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Plugin\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\Plugin\ResourceModel\Rule;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * @var Rule
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $ruleResource;

    /**
     * @var \Closure
     */
    protected $genericClosure;

    /**
     * @var MockObject
     */
    protected $abstractModel;

    /**
     * @var MockObject
     */
    private $cacheMock;

    /**
     * @var MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->ruleResource = $this->getMockBuilder(\Magento\SalesRule\Model\ResourceModel\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->genericClosure = function () {
        };

        $this->abstractModel = $this->createMock(AbstractModel::class);

        $this->cacheMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);

        $this->plugin = new \Magento\SalesRule\Model\Plugin\ResourceModel\Rule(
            $this->cacheMock,
            $this->serializerMock
        );
    }

    public function testAroundLoadCustomerGroupIds()
    {
        $this->assertEquals(
            $this->ruleResource,
            $this->plugin->aroundLoadCustomerGroupIds($this->ruleResource, $this->genericClosure, $this->abstractModel)
        );
    }

    public function testAroundLoadWebsiteIds()
    {
        $this->assertEquals(
            $this->ruleResource,
            $this->plugin->aroundLoadWebsiteIds($this->ruleResource, $this->genericClosure, $this->abstractModel)
        );
    }

    public function testBeforeSetActualProductAttributesStoresAttributes(): void
    {
        $subject = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule::class);
        $attributes = ['color', 'size'];

        $result = $this->plugin->beforeSetActualProductAttributes($subject, $this->abstractModel, $attributes);

        $this->assertEquals(null, $result);

        $reflection = new \ReflectionClass($this->plugin);
        $prop = $reflection->getProperty('attributes');

        $this->assertEquals($attributes, $prop->getValue($this->plugin));
    }

    public function testAfterSetActualProductAttributesCleansCacheWhenNewAttributesFound(): void
    {
        $subject = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule::class);

        $cachedAttributes = ['color'];
        $newAttributes = ['color', 'size'];

        // simulate attributes stored from before plugin
        $this->plugin->beforeSetActualProductAttributes($subject, $this->abstractModel, $newAttributes);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('serialized-data');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serialized-data')
            ->willReturn($cachedAttributes);

        $this->cacheMock->expects($this->once())
            ->method('clean')
            ->with([\Magento\SalesRule\Model\Plugin\ResourceModel\Rule::CACHE_TAG]);

        $result = $this->plugin->afterSetActualProductAttributes($subject, $subject);

        $this->assertSame($subject, $result);
    }

    public function testAfterSetActualProductAttributesDoesNotCleanCacheWhenAttributesSame(): void
    {
        $subject = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule::class);

        $cachedAttributes = ['color'];
        $newAttributes = ['color'];

        $this->plugin->beforeSetActualProductAttributes($subject, $this->abstractModel, $newAttributes);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('serialized-data');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($cachedAttributes);

        $this->cacheMock->expects($this->never())
            ->method('clean');

        $this->plugin->afterSetActualProductAttributes($subject, $subject);
    }

    public function testAfterSetActualProductAttributesReturnsWhenCacheMissing(): void
    {
        $subject = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule::class);

        $this->plugin->beforeSetActualProductAttributes($subject, $this->abstractModel, ['color']);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);

        $this->cacheMock->expects($this->never())
            ->method('clean');

        $result = $this->plugin->afterSetActualProductAttributes($subject, $subject);

        $this->assertSame($subject, $result);
    }
}
