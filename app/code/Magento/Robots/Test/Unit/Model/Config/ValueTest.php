<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Test\Unit\Model\Config;

use PHPUnit\Framework\TestCase;
use Magento\Robots\Model\Config\Value;
use Magento\Framework\Model\Context;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

class ValueTest extends TestCase
{
    /**
     * @var Value
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var TypeListInterface|MockObject
     */
    private $typeList;

    /**
     * @var StoreResolver|MockObject
     */
    private $storeResolver;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->typeList = $this->getMockBuilder(TypeListInterface::class)
            ->getMockForAbstractClass();

        $this->storeResolver = $this->getMockBuilder(StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Value(
            $this->context,
            $this->registry,
            $this->scopeConfig,
            $this->typeList,
            $this->storeResolver,
            $this->storeManager
        );
    }

    /**
     * Check that getIdentities() method returns specified cache tag
     */
    public function testGetIdentities()
    {
        $storeId = 1;

        $storeMock = $this->getMockBuilder(StoreInterface::class)->getMockForAbstractClass();

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $expected = [
            Value::CACHE_TAG . '_' . $storeId,
        ];
        $this->assertEquals($expected, $this->model->getIdentities());
    }
}
