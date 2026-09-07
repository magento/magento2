<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product;

use Magento\Catalog\Block\Adminhtml\Product\Price;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Product Price adminhtml block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Price
 */
class PriceTest extends TestCase
{
    /**
     * @var Price|MockObject
     */
    private MockObject $block;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private MockObject $storeManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->block = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new \ReflectionClass($this->block);
        $storeManagerProperty = $reflection->getProperty('_storeManager');
        $storeManagerProperty->setValue($this->block, $this->storeManagerMock);
    }

    /**
     * Data provider for testGetWebsiteReturnsWebsiteForStoreId
     *
     * @return array
     */
    public static function storeIdDataProvider(): array
    {
        return [
            'integer store id' => [
                'storeId' => 1
            ],
            'string store id' => [
                'storeId' => '2'
            ],
            'null store id' => [
                'storeId' => null
            ],
            'boolean false store id' => [
                'storeId' => false
            ]
        ];
    }

    /**
     * Test getWebsite returns website for given store ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Price::getWebsite
     * @param int|string|bool|null $storeId
     * @return void
     */
    #[DataProvider('storeIdDataProvider')]
    public function testGetWebsiteReturnsWebsiteForStoreId(
        int|string|bool|null $storeId
    ): void {
        $storeMock = $this->createMock(Store::class);
        $websiteMock = $this->createMock(Website::class);

        $storeMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $result = $this->block->getWebsite($storeId);

        $this->assertSame($websiteMock, $result);
    }

    /**
     * Test getWebsite throws NoSuchEntityException for invalid store ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Price::getWebsite
     * @return void
     */
    public function testGetWebsiteThrowsExceptionForInvalidStoreId(): void
    {
        $invalidStoreId = 99999;

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($invalidStoreId)
            ->willThrowException(new NoSuchEntityException(__('Store with ID "%1" not found.', $invalidStoreId)));

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Store with ID "99999" not found.');

        $this->block->getWebsite($invalidStoreId);
    }

    /**
     * Test getWebsite returns null when store has no website
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Price::getWebsite
     * @return void
     */
    public function testGetWebsiteReturnsNullWhenStoreHasNoWebsite(): void
    {
        $storeId = 1;
        $storeMock = $this->createMock(Store::class);

        $storeMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn(null);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $result = $this->block->getWebsite($storeId);

        $this->assertNull($result);
    }

    /**
     * Test getWebsite propagates exception when getWebsite call fails
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Price::getWebsite
     * @return void
     */
    public function testGetWebsitePropagatesExceptionFromStoreGetWebsite(): void
    {
        $storeId = 1;
        $storeMock = $this->createMock(Store::class);

        $storeMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new \RuntimeException('Unable to load website'));

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to load website');

        $this->block->getWebsite($storeId);
    }
}
