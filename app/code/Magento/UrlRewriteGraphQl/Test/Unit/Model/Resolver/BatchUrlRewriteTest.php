<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequest;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;
use Magento\UrlRewriteGraphQl\Model\Resolver\BatchUrlRewrite;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BatchUrlRewriteTest extends TestCase
{
    use MockCreationTrait;

    private const STORE_ID = 2;

    private const ENTITY_TYPE_MAPPING = [
        'catalog_product' => 'product',
        'catalog_category' => 'category',
    ];

    /**
     * @var array[]
     */
    private array $rows = [
        [
            UrlRewriteDTO::ENTITY_TYPE => 'product',
            UrlRewriteDTO::ENTITY_ID => '1',
            UrlRewriteDTO::STORE_ID => '2',
            UrlRewriteDTO::REQUEST_PATH => 'first.html',
            UrlRewriteDTO::TARGET_PATH => 'catalog/product/view/id/1',
            UrlRewriteDTO::REDIRECT_TYPE => '0',
        ],
        [
            UrlRewriteDTO::ENTITY_TYPE => 'product',
            UrlRewriteDTO::ENTITY_ID => '2',
            UrlRewriteDTO::STORE_ID => '2',
            UrlRewriteDTO::REQUEST_PATH => 'gear/second.html',
            UrlRewriteDTO::TARGET_PATH => 'catalog/product/view/id/2/category/5',
            UrlRewriteDTO::REDIRECT_TYPE => '0',
        ],
        [
            UrlRewriteDTO::ENTITY_TYPE => 'product',
            UrlRewriteDTO::ENTITY_ID => '1',
            UrlRewriteDTO::STORE_ID => '2',
            UrlRewriteDTO::REQUEST_PATH => 'first-old.html',
            UrlRewriteDTO::TARGET_PATH => 'first.html',
            UrlRewriteDTO::REDIRECT_TYPE => '301',
        ],
        [
            UrlRewriteDTO::ENTITY_TYPE => 'product',
            UrlRewriteDTO::ENTITY_ID => '2',
            UrlRewriteDTO::STORE_ID => '2',
            UrlRewriteDTO::REQUEST_PATH => 'second.html',
            UrlRewriteDTO::TARGET_PATH => 'catalog/product/view/id/2',
            UrlRewriteDTO::REDIRECT_TYPE => '0',
        ],
        [
            UrlRewriteDTO::ENTITY_TYPE => 'category',
            UrlRewriteDTO::ENTITY_ID => '1',
            UrlRewriteDTO::STORE_ID => '2',
            UrlRewriteDTO::REQUEST_PATH => 'gear.html',
            UrlRewriteDTO::TARGET_PATH => 'catalog/category/view/id/1',
            UrlRewriteDTO::REDIRECT_TYPE => '0',
        ],
    ];

    public function testResolvesAllEntitiesWithOneLookup(): void
    {
        $urlFinder = $this->createMock(UrlFinderInterface::class);
        $urlFinder->expects($this->once())
            ->method('findAllByData')
            ->with([
                UrlRewriteDTO::ENTITY_TYPE => 'product',
                UrlRewriteDTO::ENTITY_ID => [1, 2, 3],
                UrlRewriteDTO::STORE_ID => self::STORE_ID,
            ])
            ->willReturnCallback(fn (array $data): array => $this->findRewrites($data));

        $requests = [
            $this->createRequest($this->createEntity(1)),
            $this->createRequest($this->createEntity(2)),
            $this->createRequest($this->createEntity(3)),
            $this->createRequest($this->createEntity(1)),
        ];

        $response = $this->createBatchResolver($urlFinder)
            ->resolve($this->createContext(), $this->createStub(Field::class), $requests);

        $firstProduct = [['url' => 'first.html', 'parameters' => [['name' => 'id', 'value' => '1']]]];
        $this->assertSame($firstProduct, $response->findResponseFor($requests[0]));
        $this->assertSame(
            [
                [
                    'url' => 'gear/second.html',
                    'parameters' => [
                        ['name' => 'id', 'value' => '2'],
                        ['name' => 'category', 'value' => '5'],
                    ],
                ],
                ['url' => 'second.html', 'parameters' => [['name' => 'id', 'value' => '2']]],
            ],
            $response->findResponseFor($requests[1])
        );
        $this->assertSame([], $response->findResponseFor($requests[2]));
        $this->assertSame($firstProduct, $response->findResponseFor($requests[3]));
    }

    public function testReturnsSameResultAsPerEntityResolver(): void
    {
        $urlFinder = $this->createStub(UrlFinderInterface::class);
        $urlFinder->method('findAllByData')
            ->willReturnCallback(fn (array $data): array => $this->findRewrites($data));
        $context = $this->createContext(4);
        $field = $this->createStub(Field::class);
        $info = $this->createStub(ResolveInfo::class);
        $perEntityResolver = new UrlRewrite(
            $urlFinder,
            $this->createTypeResolver(),
            $this->createMetadataPool(),
            self::ENTITY_TYPE_MAPPING
        );

        $requests = [];
        $expected = [];
        foreach ([2, 3, 1] as $entityId) {
            $value = ['model' => $this->createEntity($entityId)];
            $requests[] = new ResolveRequest($field, $context, $info, $value, null);
            $expected[] = $perEntityResolver->resolve($field, $context, $info, $value);
        }

        $response = $this->createBatchResolver($urlFinder)->resolve($context, $field, $requests);

        foreach ($requests as $index => $request) {
            $this->assertSame($expected[$index], $response->findResponseFor($request));
        }
    }

    public function testEntityWithoutIdGetsEmptyListWithoutLookup(): void
    {
        $urlFinder = $this->createMock(UrlFinderInterface::class);
        $urlFinder->expects($this->once())
            ->method('findAllByData')
            ->with([
                UrlRewriteDTO::ENTITY_TYPE => 'product',
                UrlRewriteDTO::ENTITY_ID => [1],
                UrlRewriteDTO::STORE_ID => self::STORE_ID,
            ])
            ->willReturnCallback(fn (array $data): array => $this->findRewrites($data));
        $requests = [
            $this->createRequest($this->createEntity(null)),
            $this->createRequest($this->createEntity(1)),
        ];

        $response = $this->createBatchResolver($urlFinder)
            ->resolve($this->createContext(), $this->createStub(Field::class), $requests);

        $this->assertSame([], $response->findResponseFor($requests[0]));
        $this->assertSame(
            [['url' => 'first.html', 'parameters' => [['name' => 'id', 'value' => '1']]]],
            $response->findResponseFor($requests[1])
        );
    }

    public function testNoLookupWhenNoEntityHasId(): void
    {
        $urlFinder = $this->createMock(UrlFinderInterface::class);
        $urlFinder->expects($this->never())->method('findAllByData');
        $request = $this->createRequest($this->createEntity(null));

        $response = $this->createBatchResolver($urlFinder)
            ->resolve($this->createContext(), $this->createStub(Field::class), [$request]);

        $this->assertSame([], $response->findResponseFor($request));
    }

    public function testOneLookupPerEntityType(): void
    {
        $urlFinder = $this->createMock(UrlFinderInterface::class);
        $urlFinder->expects($this->exactly(2))
            ->method('findAllByData')
            ->willReturnCallback(function (array $data): array {
                $expectedIds = ['product' => [1, 2], 'category' => [1]];
                $this->assertSame($expectedIds[$data[UrlRewriteDTO::ENTITY_TYPE]], $data[UrlRewriteDTO::ENTITY_ID]);
                $this->assertSame(self::STORE_ID, $data[UrlRewriteDTO::STORE_ID]);

                return $this->findRewrites($data);
            });
        $requests = [
            $this->createRequest($this->createEntity(1)),
            $this->createRequest($this->createEntity(1, 'catalog_category')),
            $this->createRequest($this->createEntity(2)),
        ];

        $response = $this->createBatchResolver($urlFinder)
            ->resolve($this->createContext(), $this->createStub(Field::class), $requests);

        $this->assertSame(
            [['url' => 'first.html', 'parameters' => [['name' => 'id', 'value' => '1']]]],
            $response->findResponseFor($requests[0])
        );
        $this->assertSame(
            [['url' => 'gear.html', 'parameters' => [['name' => 'id', 'value' => '1']]]],
            $response->findResponseFor($requests[1])
        );
        $this->assertSame(
            [
                [
                    'url' => 'gear/second.html',
                    'parameters' => [
                        ['name' => 'id', 'value' => '2'],
                        ['name' => 'category', 'value' => '5'],
                    ],
                ],
                ['url' => 'second.html', 'parameters' => [['name' => 'id', 'value' => '2']]],
            ],
            $response->findResponseFor($requests[2])
        );
    }

    public function testStringEntityIds(): void
    {
        $lookups = [];
        $urlFinder = $this->createMock(UrlFinderInterface::class);
        $urlFinder->expects($this->once())
            ->method('findAllByData')
            ->willReturnCallback(function (array $data) use (&$lookups): array {
                $lookups[] = $data;

                return $this->findRewrites($data);
            });
        $requests = [
            $this->createRequest($this->createEntity('2')),
            $this->createRequest($this->createEntity('1')),
            $this->createRequest($this->createEntity(1)),
        ];

        $response = $this->createBatchResolver($urlFinder)
            ->resolve($this->createContext(), $this->createStub(Field::class), $requests);

        $this->assertSame(
            [
                [
                    UrlRewriteDTO::ENTITY_TYPE => 'product',
                    UrlRewriteDTO::ENTITY_ID => ['2', '1'],
                    UrlRewriteDTO::STORE_ID => self::STORE_ID,
                ],
            ],
            $lookups
        );
        $firstProduct = [['url' => 'first.html', 'parameters' => [['name' => 'id', 'value' => '1']]]];
        $this->assertCount(2, $response->findResponseFor($requests[0]));
        $this->assertSame($firstProduct, $response->findResponseFor($requests[1]));
        $this->assertSame($firstProduct, $response->findResponseFor($requests[2]));
    }

    public function testThrowsWhenModelIsMissing(): void
    {
        $urlFinder = $this->createMock(UrlFinderInterface::class);
        $urlFinder->expects($this->never())->method('findAllByData');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');

        $this->createBatchResolver($urlFinder)->resolve(
            $this->createStub(ContextInterface::class),
            $this->createStub(Field::class),
            [$this->createRequest($this->createEntity(1)), $this->createRequest(null)]
        );
    }

    public function testThrowsForUnmappedEntityType(): void
    {
        $urlFinder = $this->createMock(UrlFinderInterface::class);
        $urlFinder->expects($this->never())->method('findAllByData');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('URL rewrite entity type is not configured for "cms_page"');

        $this->createBatchResolver($urlFinder)->resolve(
            $this->createStub(ContextInterface::class),
            $this->createStub(Field::class),
            [$this->createRequest($this->createEntity(1, 'cms_page'))]
        );
    }

    private function createBatchResolver(UrlFinderInterface $urlFinder): BatchUrlRewrite
    {
        return new BatchUrlRewrite(
            $urlFinder,
            $this->createTypeResolver(),
            $this->createMetadataPool(),
            self::ENTITY_TYPE_MAPPING
        );
    }

    /**
     * The resolved "type" is the entity's EAV type, which createMetadataPool() hands back as metadata.
     */
    private function createTypeResolver(): TypeResolver
    {
        $typeResolver = $this->createStub(TypeResolver::class);
        $typeResolver->method('resolve')->willReturnCallback(fn (object $entity): string => $entity->eavEntityType);

        return $typeResolver;
    }

    private function createMetadataPool(): MetadataPool
    {
        $metadataPool = $this->createStub(MetadataPool::class);
        $metadataPool->method('getMetadata')->willReturnCallback(
            function (string $eavEntityType): EntityMetadataInterface {
                $metadata = $this->createStub(EntityMetadataInterface::class);
                $metadata->method('getEavEntityType')->willReturn($eavEntityType);

                return $metadata;
            }
        );

        return $metadataPool;
    }

    private function createContext(int $storeReads = 1): ContextInterface
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(self::STORE_ID);
        $extensionAttributes = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['getStore']
        );
        $extensionAttributes->expects($this->exactly($storeReads))->method('getStore')->willReturn($store);
        $context = $this->createStub(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn($extensionAttributes);

        return $context;
    }

    private function createEntity(int|string|null $entityId, string $eavEntityType = 'catalog_product'): AbstractModel
    {
        return new class ($entityId, $eavEntityType) extends AbstractModel {
            public function __construct(
                private readonly int|string|null $id,
                public readonly string $eavEntityType
            ) {
            }

            /**
             * @inheritdoc
             */
            public function getEntityId()
            {
                return $this->id;
            }
        };
    }

    private function createRequest(?AbstractModel $entity): ResolveRequest
    {
        return new ResolveRequest(
            $this->createStub(Field::class),
            $this->createStub(ContextInterface::class),
            $this->createStub(ResolveInfo::class),
            $entity === null ? [] : ['model' => $entity],
            null
        );
    }

    /**
     * Emulates DbStorage, which turns every filter value into an IN condition.
     *
     * @param array $data
     * @return UrlRewriteDTO[]
     */
    private function findRewrites(array $data): array
    {
        $rewrites = [];
        foreach ($this->rows as $row) {
            foreach ($data as $column => $value) {
                if (!in_array((string)$row[$column], array_map('strval', (array)$value), true)) {
                    continue 2;
                }
            }
            $rewrites[] = new UrlRewriteDTO($row, new Json());
        }

        return $rewrites;
    }
}
