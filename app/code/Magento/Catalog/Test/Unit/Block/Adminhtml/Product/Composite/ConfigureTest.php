<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Composite;

use Magento\Catalog\Block\Adminhtml\Product\Composite\Configure;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Configure block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Composite\Configure
 */
class ConfigureTest extends TestCase
{
    /**
     * @var Configure
     */
    private Configure $block;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var MockObject&Product
     */
    private MockObject $catalogProductMock;

    /**
     * @var MockObject&Registry
     */
    private MockObject $registryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Prepare ObjectManager for helpers used by parent blocks
        $objects = [
            [JsonHelper::class, $this->createMock(JsonHelper::class)],
            [DirectoryHelper::class, $this->createMock(DirectoryHelper::class)]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->catalogProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $this->objectManager->getObject(
            Configure::class,
            [
                'product' => $this->catalogProductMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * Data provider for getProduct registry scenarios
     *
     * @return array<string, array<string, bool|int>>
     */
    public static function getProductRegistryScenariosDataProvider(): array
    {
        return [
            'registry has current_product returns registry product' => [
                'hasRegistryProduct' => true,
                'expectedRegistryCalls' => 2
            ],
            'registry has no current_product returns catalog product' => [
                'hasRegistryProduct' => false,
                'expectedRegistryCalls' => 1
            ]
        ];
    }

    /**
     * Test getProduct returns correct product based on registry state
     *
     * @dataProvider getProductRegistryScenariosDataProvider
     * @param bool $hasRegistryProduct
     * @param int $expectedRegistryCalls
     * @return void
     */
    public function testGetProductReturnsCorrectProductBasedOnRegistryState(
        bool $hasRegistryProduct,
        int $expectedRegistryCalls
    ): void {
        $registryProductMock = null;
        if ($hasRegistryProduct) {
            $registryProductMock = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $this->registryMock->expects($this->exactly($expectedRegistryCalls))
            ->method('registry')
            ->with('current_product')
            ->willReturn($registryProductMock);

        $result = $this->block->getProduct();

        if ($hasRegistryProduct) {
            $this->assertSame($registryProductMock, $result);
        } else {
            $this->assertSame($this->catalogProductMock, $result);
        }
    }

    /**
     * Test getProduct returns previously set product without checking registry
     *
     * @return void
     */
    public function testGetProductReturnsPreviouslySetProductWithoutRegistryCheck(): void
    {
        $customProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block->setProduct($customProductMock);

        $this->registryMock->expects($this->never())
            ->method('registry');

        $result = $this->block->getProduct();

        $this->assertSame($customProductMock, $result);
    }

    /**
     * Test getProduct caches result and returns same instance on subsequent calls
     *
     * @return void
     */
    public function testGetProductCachesResultOnSubsequentCalls(): void
    {
        $registryProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Registry is called twice on first getProduct call, never on second due to caching
        $this->registryMock->expects($this->exactly(2))
            ->method('registry')
            ->with('current_product')
            ->willReturn($registryProductMock);

        $firstResult = $this->block->getProduct();
        $secondResult = $this->block->getProduct();

        $this->assertSame($firstResult, $secondResult);
    }

    /**
     * Data provider for setProduct scenarios
     *
     * @return array<string, array<string, bool>>
     */
    public static function setProductScenariosDataProvider(): array
    {
        return [
            'setProduct with product mock returns self' => [
                'setNull' => false
            ],
            'setProduct with null value returns self' => [
                'setNull' => true
            ]
        ];
    }

    /**
     * Test setProduct sets product and returns self for method chaining
     *
     * @dataProvider setProductScenariosDataProvider
     * @param bool $setNull
     * @return void
     */
    public function testSetProductReturnsBlockInstance(bool $setNull): void
    {
        $productMock = null;
        if (!$setNull) {
            $productMock = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $result = $this->block->setProduct($productMock);

        $this->assertSame($this->block, $result);
    }

    /**
     * Test setProduct with null triggers registry lookup on next getProduct call
     *
     * @return void
     */
    public function testSetProductWithNullTriggersRegistryLookupOnNextGetProduct(): void
    {
        $registryProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        // First set a product, then reset to null
        $initialProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block->setProduct($initialProductMock);
        $this->block->setProduct(null);

        // Registry is called twice: once in if condition, once to assign
        $this->registryMock->expects($this->exactly(2))
            ->method('registry')
            ->with('current_product')
            ->willReturn($registryProductMock);

        $result = $this->block->getProduct();

        $this->assertSame($registryProductMock, $result);
    }

    /**
     * Test getProduct returns Product instance type
     *
     * @return void
     */
    public function testGetProductReturnsProductInstanceType(): void
    {
        $this->registryMock->method('registry')
            ->with('current_product')
            ->willReturn(null);

        $result = $this->block->getProduct();

        $this->assertInstanceOf(Product::class, $result);
    }
}
