<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryReplaceCoordinator;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryReplaceRolePlan;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaGalleryReplaceCoordinatorTest extends TestCase
{
    /**
     * @var MediaGalleryProcessor|MockObject
     */
    private $mediaProcessor;

    /**
     * @var MediaGalleryReplaceCoordinator
     */
    private $coordinator;

    protected function setUp(): void
    {
        $this->mediaProcessor = $this->createMock(MediaGalleryProcessor::class);

        $metadata = $this->createStub(EntityMetadata::class);
        $metadata->method('getLinkField')->willReturn('entity_id');
        $metadataPool = $this->createStub(MetadataPool::class);
        $metadataPool->method('getMetadata')->willReturn($metadata);

        $this->coordinator = new MediaGalleryReplaceCoordinator(
            new MediaGalleryReplaceRolePlan($this->mediaProcessor),
            $metadataPool
        );
    }

    public function testCollectRemovalsIsNoOpWhenDisabled(): void
    {
        $this->mediaProcessor->expects($this->never())->method('getProductImageRoles');

        $this->coordinator->configure(false, ['image']);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'a.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'a.jpg');

        $this->assertFalse($this->coordinator->hasRegisteredProducts());
        $this->assertSame([[], []], $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'old.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/old.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]));
    }

    public function testRegisterProductIgnoresRowsWithoutAdditionalImagesColumn(): void
    {
        $this->mediaProcessor->expects($this->never())->method('getProductImageRoles');

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->registerProduct(
            'SKU1',
            ['image' => '/i/m/img.jpg'],
            Store::DEFAULT_STORE_ID
        );

        $this->assertFalse($this->coordinator->hasRegisteredProducts());
    }

    public function testRegisterProductIgnoresNonDefaultStore(): void
    {
        $this->mediaProcessor->expects($this->never())->method('getProductImageRoles');

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'a.jpg'],
            1
        );

        $this->assertFalse($this->coordinator->hasRegisteredProducts());
    }

    public function testCollectRemovalsDropsUnlistedImagesAndKeepsProtectedRoles(): void
    {
        $this->mediaProcessor->expects($this->once())
            ->method('getProductImageRoles')
            ->with(['SKU1'], ['image', 'small_image', 'thumbnail', 'swatch_image'])
            ->willReturn([
                'sku1' => [
                    'image' => [Store::DEFAULT_STORE_ID => '/r/o/role.jpg'],
                ],
            ]);

        $this->coordinator->configure(true, ['image', 'small_image', 'thumbnail', 'swatch_image']);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'new.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        [$removals, $removedSkus] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'r/o/role.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/r/o/role.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old_extra.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/o/l/old_extra.jpg',
                        'media_type' => 'image',
                    ],
                    'v/i/video.mp4' => [
                        'value_id' => 3,
                        'entity_id' => 10,
                        'value' => '/v/i/video.mp4',
                        'media_type' => 'external-video',
                    ],
                    'n/e/new.jpg' => [
                        'value_id' => 4,
                        'entity_id' => 10,
                        'value' => '/n/e/new.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertSame(['SKU1'], $removedSkus);
        $this->assertCount(1, $removals);
        $this->assertSame(2, (int)$removals[0]['value_id']);
    }

    public function testWarmRolesCacheUsesBulkLoad(): void
    {
        $this->mediaProcessor->expects($this->once())
            ->method('getProductImageRoles')
            ->with(['SKU1'], ['image'])
            ->willReturn([
                'sku1' => [
                    'image' => [Store::DEFAULT_STORE_ID => '/r/o/role.jpg'],
                ],
            ]);

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->warmRolesCache(['SKU1']);
        $this->coordinator->registerProduct(
            'SKU1',
            ['additional_images' => 'new.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        [$removals] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'r/o/role.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/r/o/role.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/o/l/old.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $removals);
        $this->assertSame(2, (int)$removals[0]['value_id']);
    }

    public function testWarmRolesCacheAppendsWithoutReloadingCachedSkus(): void
    {
        $this->mediaProcessor->expects($this->exactly(2))
            ->method('getProductImageRoles')
            ->willReturnCallback(static function (array $skus) {
                if ($skus === ['SKU1']) {
                    return ['sku1' => ['image' => [Store::DEFAULT_STORE_ID => '/r/o/role1.jpg']]];
                }
                if ($skus === ['SKU2']) {
                    return ['sku2' => ['image' => [Store::DEFAULT_STORE_ID => '/r/o/role2.jpg']]];
                }
                self::fail('Unexpected SKU list: ' . implode(',', $skus));
            });

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->warmRolesCache(['SKU1']);
        $this->coordinator->warmRolesCache(['SKU1', 'SKU2']);
        $this->coordinator->registerProduct('SKU1', [Product::COL_MEDIA_IMAGE => 'new1.jpg'], Store::DEFAULT_STORE_ID);
        $this->coordinator->registerProduct('SKU2', [Product::COL_MEDIA_IMAGE => 'new2.jpg'], Store::DEFAULT_STORE_ID);
        $this->coordinator->keepPath('SKU1', 'n/e/new1.jpg');
        $this->coordinator->keepPath('SKU2', 'n/e/new2.jpg');

        [$removals, $removedSkus] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'r/o/role1.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/r/o/role1.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old1.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/o/l/old1.jpg',
                        'media_type' => 'image',
                    ],
                ],
                'sku2' => [
                    'r/o/role2.jpg' => [
                        'value_id' => 3,
                        'entity_id' => 20,
                        'value' => '/r/o/role2.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old2.jpg' => [
                        'value_id' => 4,
                        'entity_id' => 20,
                        'value' => '/o/l/old2.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['SKU1', 'SKU2'], $removedSkus);
        $this->assertEqualsCanonicalizing([2, 4], array_map('intval', array_column($removals, 'value_id')));
    }

    public function testKeepPathBeforeRegisterIsAppliedWhenSkuIsRegistered(): void
    {
        $this->mediaProcessor->method('getProductImageRoles')->willReturn(['sku1' => []]);

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->keepPath('SKU1', 's/t/store_added.jpg');
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'new.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        [$removals] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    's/t/store_added.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/s/t/store_added.jpg',
                        'media_type' => 'image',
                    ],
                    'n/e/new.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/n/e/new.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old.jpg' => [
                        'value_id' => 3,
                        'entity_id' => 10,
                        'value' => '/o/l/old.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $removals);
        $this->assertSame(3, (int)$removals[0]['value_id']);
    }

    public function testUploadFailureSkipsRemovalsForSku(): void
    {
        $this->mediaProcessor->method('getProductImageRoles')->willReturn(['sku1' => []]);

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'good.jpg,bad.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'g/o/good.jpg');
        $this->coordinator->markUploadFailed('SKU1');

        $this->assertSame(['SKU1'], $this->coordinator->getSkusWithSkippedRemovals());

        [$removals, $removedSkus] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'g/o/good.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/g/o/good.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/o/l/old.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertSame([], $removals);
        $this->assertSame([], $removedSkus);
    }

    public function testConfigureClearsPreviousImportState(): void
    {
        $this->mediaProcessor->method('getProductImageRoles')->willReturn([
            'sku1' => ['image' => [Store::DEFAULT_STORE_ID => '/r/o/role.jpg']],
        ]);

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->warmRolesCache(['SKU1']);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'a.jpg'],
            Store::DEFAULT_STORE_ID
        );

        $this->coordinator->configure(false, ['image']);
        $this->assertFalse($this->coordinator->hasRegisteredProducts());
        $this->assertSame([[], []], $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'old.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/old.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]));
    }

    public function testDefaultRoleReassignmentDropsOldDefaultRolePath(): void
    {
        $this->mediaProcessor->method('getProductImageRoles')->willReturn([
            'sku1' => ['image' => [Store::DEFAULT_STORE_ID => '/r/o/role.jpg']],
        ]);

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->planRoleAssignments([
            'sku1' => [Store::DEFAULT_STORE_ID => ['image' => '/n/e/new.jpg']],
        ]);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'new.jpg', 'image' => '/n/e/new.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        [$removals] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'r/o/role.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/r/o/role.jpg',
                        'media_type' => 'image',
                    ],
                    'n/e/new.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/n/e/new.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $removals);
        $this->assertSame(1, (int)$removals[0]['value_id']);
    }

    public function testNonDefaultStoreRoleIsProtectedWhenNotReassigned(): void
    {
        $this->mediaProcessor->method('getProductImageRoles')->willReturn([
            'sku1' => [
                'image' => [
                    Store::DEFAULT_STORE_ID => '/d/e/default_role.jpg',
                    1 => '/s/t/store_role.jpg',
                ],
            ],
        ]);

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->planRoleAssignments([
            'sku1' => [Store::DEFAULT_STORE_ID => ['image' => '/n/e/new.jpg']],
        ]);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'new.jpg', 'image' => '/n/e/new.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        [$removals] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'd/e/default_role.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/d/e/default_role.jpg',
                        'media_type' => 'image',
                    ],
                    's/t/store_role.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/s/t/store_role.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old_extra.jpg' => [
                        'value_id' => 3,
                        'entity_id' => 10,
                        'value' => '/o/l/old_extra.jpg',
                        'media_type' => 'image',
                    ],
                    'n/e/new.jpg' => [
                        'value_id' => 4,
                        'entity_id' => 10,
                        'value' => '/n/e/new.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertEqualsCanonicalizing([1, 3], array_map('intval', array_column($removals, 'value_id')));
    }

    public function testNonDefaultStoreRoleReassignmentAllowsDroppingOldStoreRolePath(): void
    {
        $this->mediaProcessor->method('getProductImageRoles')->willReturn([
            'sku1' => [
                'image' => [
                    Store::DEFAULT_STORE_ID => '/d/e/default_role.jpg',
                    1 => '/s/t/store_role.jpg',
                ],
            ],
        ]);

        $this->coordinator->configure(true, ['image']);
        $this->coordinator->planRoleAssignments([
            'sku1' => [
                Store::DEFAULT_STORE_ID => ['image' => '/n/e/new.jpg'],
                1 => ['image' => '/n/e/new.jpg'],
            ],
        ]);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'new.jpg', 'image' => '/n/e/new.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        [$removals] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'd/e/default_role.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/d/e/default_role.jpg',
                        'media_type' => 'image',
                    ],
                    's/t/store_role.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/s/t/store_role.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old_extra.jpg' => [
                        'value_id' => 3,
                        'entity_id' => 10,
                        'value' => '/o/l/old_extra.jpg',
                        'media_type' => 'image',
                    ],
                    'n/e/new.jpg' => [
                        'value_id' => 4,
                        'entity_id' => 10,
                        'value' => '/n/e/new.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertEqualsCanonicalizing([1, 2, 3], array_map('intval', array_column($removals, 'value_id')));
    }

    public function testAccumulatesKeepAndRolePlanAcrossSimulatedBunches(): void
    {
        $this->mediaProcessor->expects($this->once())
            ->method('getProductImageRoles')
            ->willReturn([
                'sku1' => [
                    'image' => [
                        Store::DEFAULT_STORE_ID => '/d/e/default_role.jpg',
                        1 => '/s/t/store_role.jpg',
                    ],
                ],
            ]);

        $this->coordinator->configure(true, ['image']);

        // Bunch 1
        $this->coordinator->planRoleAssignments([
            'sku1' => [Store::DEFAULT_STORE_ID => ['image' => '/n/e/new.jpg']],
        ]);
        $this->coordinator->warmRolesCache(['SKU1']);
        $this->coordinator->registerProduct(
            'SKU1',
            [Product::COL_MEDIA_IMAGE => 'new.jpg', 'image' => '/n/e/new.jpg'],
            Store::DEFAULT_STORE_ID
        );
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        // Bunch 2
        $this->coordinator->planRoleAssignments([
            'sku1' => [1 => ['image' => '/n/e/new.jpg']],
        ]);
        $this->coordinator->keepPath('SKU1', 'n/e/new.jpg');

        $this->assertTrue($this->coordinator->hasRegisteredProducts());
        $this->assertSame(['SKU1'], $this->coordinator->getRegisteredSkus());

        [$removals] = $this->coordinator->collectRemovals([
            Store::DEFAULT_STORE_ID => [
                'sku1' => [
                    'd/e/default_role.jpg' => [
                        'value_id' => 1,
                        'entity_id' => 10,
                        'value' => '/d/e/default_role.jpg',
                        'media_type' => 'image',
                    ],
                    's/t/store_role.jpg' => [
                        'value_id' => 2,
                        'entity_id' => 10,
                        'value' => '/s/t/store_role.jpg',
                        'media_type' => 'image',
                    ],
                    'o/l/old_extra.jpg' => [
                        'value_id' => 3,
                        'entity_id' => 10,
                        'value' => '/o/l/old_extra.jpg',
                        'media_type' => 'image',
                    ],
                    'n/e/new.jpg' => [
                        'value_id' => 4,
                        'entity_id' => 10,
                        'value' => '/n/e/new.jpg',
                        'media_type' => 'image',
                    ],
                ],
            ],
        ]);

        $this->assertEqualsCanonicalizing([1, 2, 3], array_map('intval', array_column($removals, 'value_id')));
    }
}
