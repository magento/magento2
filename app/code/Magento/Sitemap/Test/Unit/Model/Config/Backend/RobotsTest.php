<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Model\Config\Backend\Robots;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RobotsTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Robots
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeListMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);

        $this->registryMock = $this->createMock(Registry::class);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->cacheTypeListMock = $this->createMock(TypeListInterface::class);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Robots::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'config' => $this->scopeConfigMock,
                'cacheTypeList' => $this->cacheTypeListMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Check that getIdentities() method returns specified cache tag
     */
    public function testGetIdentities()
    {
        $storeId = 1;

        $storeMock = $this->createMock(StoreInterface::class);

        $this->storeManagerMock->expects($this->once())
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
