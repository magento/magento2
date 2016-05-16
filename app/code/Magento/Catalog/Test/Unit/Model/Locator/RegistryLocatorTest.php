<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
class RegistryLocatorTest extends \PHPUnit_Framework_TestCase
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
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    protected function setUp()
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
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Product was not registered
     */
    public function testGetProductWithException()
    {
        $this->assertInstanceOf(ProductInterface::class, $this->model->getProduct());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Store was not registered
     */
    public function testGetStoreWithException()
    {
        $this->assertInstanceOf(StoreInterface::class, $this->model->getStore());
    }
}
