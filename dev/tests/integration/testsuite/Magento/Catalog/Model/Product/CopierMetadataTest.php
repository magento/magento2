<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for product metadata preservation during duplication.
 *
 * @see https://github.com/magento/magento2/issues/40402
 */
class CopierMetadataTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Copier
     */
    private $copier;

    /**
     * @var string|null
     */
    private $duplicatedSku;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->copier = $this->objectManager->get(Copier::class);
    }

    protected function tearDown(): void
    {
        if ($this->duplicatedSku !== null) {
            try {
                $this->productRepository->deleteById($this->duplicatedSku);
            } catch (NoSuchEntityException $e) {
                // already cleaned up
            }
        }
    }

    /**
     * Verify that meta_title, meta_keyword, meta_description are copied to the duplicate product.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea('adminhtml'),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Source Product',
                'sku' => 'source-product-meta',
                'custom_attributes' => [
                    'tax_class_id' => '2',
                    'meta_title' => 'Original Meta Title',
                    'meta_keyword' => 'keyword1, keyword2',
                    'meta_description' => 'Original meta description for the product.',
                ],
            ],
            'product'
        ),
    ]
    public function testDuplicatePreservesMetadata(): void
    {
        $sourceProduct = $this->fixtures->get('product');
        $source = $this->productRepository->getById($sourceProduct->getId());

        $this->assertSame('Original Meta Title', $source->getMetaTitle());
        $this->assertSame('keyword1, keyword2', $source->getMetaKeyword());
        $this->assertSame('Original meta description for the product.', $source->getMetaDescription());

        $duplicate = $this->copier->copy($source);
        $this->duplicatedSku = $duplicate->getSku();

        $duplicateReloaded = $this->productRepository->getById($duplicate->getId());

        $this->assertSame(
            'Original Meta Title',
            $duplicateReloaded->getMetaTitle(),
            'meta_title must be copied to the duplicate product'
        );
        $this->assertSame(
            'keyword1, keyword2',
            $duplicateReloaded->getMetaKeyword(),
            'meta_keyword must be copied to the duplicate product'
        );
        $this->assertSame(
            'Original meta description for the product.',
            $duplicateReloaded->getMetaDescription(),
            'meta_description must be copied to the duplicate product'
        );
    }

    /**
     * Verify that other core attributes (name, price, weight) are still copied correctly.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea('adminhtml'),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Core Attrs Product',
                'sku' => 'source-product-core',
                'price' => 99.99,
                'weight' => 2.5,
                'custom_attributes' => [
                    'tax_class_id' => '2',
                    'meta_title' => 'Test Meta',
                ],
            ],
            'product'
        ),
    ]
    public function testDuplicatePreservesCoreAttributes(): void
    {
        $source = $this->fixtures->get('product');
        $sourceProduct = $this->productRepository->getById($source->getId());

        $duplicate = $this->copier->copy($sourceProduct);
        $this->duplicatedSku = $duplicate->getSku();

        $duplicateReloaded = $this->productRepository->getById($duplicate->getId());

        $this->assertSame('Core Attrs Product', $duplicateReloaded->getName());
        $this->assertEquals(99.99, (float)$duplicateReloaded->getPrice());
        $this->assertEquals(2.5, (float)$duplicateReloaded->getWeight());
        $this->assertSame($sourceProduct->getAttributeSetId(), $duplicateReloaded->getAttributeSetId());
    }
}
