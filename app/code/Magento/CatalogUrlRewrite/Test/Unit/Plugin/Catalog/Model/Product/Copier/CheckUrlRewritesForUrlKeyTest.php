<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Plugin\Catalog\Model\Product\Copier;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Copier\UrlKeyAvailability;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Plugin\Catalog\Model\Product\Copier\CheckUrlRewritesForUrlKey;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class CheckUrlRewritesForUrlKeyTest extends TestCase
{
    /**
     * @var ProductUrlPathGenerator|Stub
     */
    private $productUrlPathGenerator;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $urlFinder;

    /**
     * @var CheckUrlRewritesForUrlKey
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->productUrlPathGenerator = $this->createStub(ProductUrlPathGenerator::class);
        $this->productUrlPathGenerator->method('getUrlPathWithSuffix')
            ->willReturnCallback(static fn (...$arguments): string => 'key-1'
                . ($arguments[1] === 2 ? '.htm' : '.html'));
        $this->urlFinder = $this->createMock(UrlFinderInterface::class);
        $this->plugin = new CheckUrlRewritesForUrlKey($this->productUrlPathGenerator, $this->urlFinder);
    }

    public function testKeepsUnavailableResultWithoutLookup(): void
    {
        $this->urlFinder->expects($this->never())->method('findAllByData');

        $this->assertFalse(
            $this->plugin->afterIsAvailable(
                $this->createStub(UrlKeyAvailability::class),
                false,
                $this->createDuplicate(0, null)
            )
        );
    }

    /**
     * @param int $storeId
     * @param int|null $duplicateId
     * @param array $expectedFilter
     * @param array $rewrites
     * @param bool $expected
     * @return void
     */
    #[DataProvider('rewritesDataProvider')]
    public function testChecksRequestPathsPerStore(
        int $storeId,
        ?int $duplicateId,
        array $expectedFilter,
        array $rewrites,
        bool $expected
    ): void {
        $serializer = $this->createStub(Json::class);
        $this->urlFinder->expects($this->once())
            ->method('findAllByData')
            ->with($expectedFilter)
            ->willReturn(array_map(static fn (array $data) => new UrlRewrite($data, $serializer), $rewrites));

        $this->assertSame(
            $expected,
            $this->plugin->afterIsAvailable(
                $this->createStub(UrlKeyAvailability::class),
                true,
                $this->createDuplicate($storeId, $duplicateId)
            )
        );
    }

    /**
     * @return array
     */
    public static function rewritesDataProvider(): array
    {
        $allStores = [
            UrlRewrite::REQUEST_PATH => ['key-1.html', 'key-1.htm'],
            UrlRewrite::STORE_ID => [1, 2],
        ];
        $secondStore = [
            UrlRewrite::REQUEST_PATH => ['key-1.htm'],
            UrlRewrite::STORE_ID => [2],
        ];

        return [
            'no rewrites' => [0, null, $allStores, [], true],
            'custom redirect in a store' => [
                0,
                null,
                $allStores,
                [self::rewrite('key-1.html', 1, 'custom', 0)],
                false,
            ],
            'category with the same path' => [
                0,
                null,
                $allStores,
                [self::rewrite('key-1.htm', 2, 'category', 5)],
                false,
            ],
            'historical redirect of another product' => [
                0,
                null,
                $allStores,
                [self::rewrite('key-1.html', 1, 'product', 7)],
                false,
            ],
            'path of one store used in a store with another suffix' => [
                0,
                null,
                $allStores,
                [self::rewrite('key-1.html', 2, 'product', 7)],
                true,
            ],
            'store scope checks only that store' => [
                2,
                10,
                $secondStore,
                [self::rewrite('key-1.htm', 2, 'cms-page', 3)],
                false,
            ],
            'own rewrite of the duplicate' => [
                2,
                10,
                $secondStore,
                [self::rewrite('key-1.htm', 2, 'product', 10)],
                true,
            ],
        ];
    }

    /**
     * @param string $requestPath
     * @param int $storeId
     * @param string $entityType
     * @param int $entityId
     * @return array
     */
    private static function rewrite(string $requestPath, int $storeId, string $entityType, int $entityId): array
    {
        return [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::STORE_ID => $storeId,
            UrlRewrite::ENTITY_TYPE => $entityType,
            UrlRewrite::ENTITY_ID => $entityId,
        ];
    }

    /**
     * @param int $storeId
     * @param int|null $id
     * @return Product
     */
    private function createDuplicate(int $storeId, ?int $id): Product
    {
        $duplicate = $this->createStub(Product::class);
        $duplicate->method('getStoreId')->willReturn($storeId);
        $duplicate->method('getStoreIds')->willReturn(['1', '2']);
        $duplicate->method('getId')->willReturn($id);

        return $duplicate;
    }
}
