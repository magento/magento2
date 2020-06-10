<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Locator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\RegistryLocator;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegistryLocatorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var RegistryLocator
     */
    protected $model;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->setMethods(['registry'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $this->model = $this->objectManager->getObject(RegistryLocator::class, [
            'registry' => $this->registryMock,
        ]);
    }

    public function testGetProduct()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->willReturn($this->productMock);

        $this->assertInstanceOf(ProductInterface::class, $this->model->getProduct());
        // Lazy loading
        $this->assertInstanceOf(ProductInterface::class, $this->model->getProduct());
    }

    public function testGetStore()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_store')
            ->willReturn($this->storeMock);

        $this->assertInstanceOf(StoreInterface::class, $this->model->getStore());
        // Lazy loading
        $this->assertInstanceOf(StoreInterface::class, $this->model->getStore());
    }

    public function testGetProductWithException()
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $this->expectExceptionMessage('The product wasn\'t registered.');
        $this->assertInstanceOf(ProductInterface::class, $this->model->getProduct());
    }

    public function testGetStoreWithException()
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $this->expectExceptionMessage('The store wasn\'t registered. Verify the store and try again.');
        $this->assertInstanceOf(StoreInterface::class, $this->model->getStore());
    }
}
