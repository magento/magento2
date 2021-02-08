<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Locator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\RegistryLocator;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class RegistryLocatorTest
 */
class RegistryLocatorTest extends \PHPUnit\Framework\TestCase
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
     * @var Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var ProductInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var StoreInterface|\PHPUnit\Framework\MockObject\MockObject
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

    /**
     */
    public function testGetProductWithException()
    {
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);
        $this->expectExceptionMessage('The product wasn\'t registered.');

        $this->assertInstanceOf(ProductInterface::class, $this->model->getProduct());
    }

    /**
     */
    public function testGetStoreWithException()
    {
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);
        $this->expectExceptionMessage('The store wasn\'t registered. Verify the store and try again.');

        $this->assertInstanceOf(StoreInterface::class, $this->model->getStore());
    }
}
