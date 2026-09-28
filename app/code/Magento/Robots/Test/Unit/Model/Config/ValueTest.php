<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Robots\Test\Unit\Model\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Robots\Model\Config\Value;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $this->context = $this->createMock(Context::class);

        $this->registry = $this->createMock(Registry::class);

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->typeList = $this->createMock(TypeListInterface::class);

        $this->storeResolver = $this->createMock(StoreResolver::class);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);

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

        $storeMock = $this->createMock(StoreInterface::class);

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
