<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Option;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AreBundleOptionsSalableTest extends TestCase
{
    /**
     * @var AreBundleOptionsSalable
     */
    private $areBundleOptionsSalable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    protected function setUp(): void
    {
        $this->areBundleOptionsSalable = Bootstrap::getObjectManager()->create(AreBundleOptionsSalable::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
    }

    #[
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$', 'code' => 'store2'], 'store2'),
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'website_ids' => [1, '$website2.id']], 's1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'website_ids' => [1, '$website2.id']], 's2'),
        DataFixture(ProductFixture::class, ['sku' => 'simple3', 'website_ids' => [1, '$website2.id']], 's3'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s1.sku$'], 'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s2.sku$'], 'link2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s3.sku$'], 'link3'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link3$'], 'required' => false], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle1', '_options' => ['$opt1$', '$opt2$'], 'website_ids' => [1, '$website2.id']]
        ),
    ]
    /**
     * @param string $storeCodeForChange
     * @param array $disabledChildren
     * @param string $storeCodeForCheck
     * @param bool $expectedResult
     * @return void
     */
    #[DataProvider('executeDataProvider')]
    public function testExecute(
        string $storeCodeForChange,
        array $disabledChildren,
        string $storeCodeForCheck,
        bool $expectedResult
    ): void {
        $storeForChange = $this->storeRepository->get($storeCodeForChange);
        foreach ($disabledChildren as $childSku) {
            $child = $this->productRepository->get($childSku, true, $storeForChange->getId(), true);
            $child->setStatus(ProductStatus::STATUS_DISABLED);
            $this->productRepository->save($child);
        }

        $bundle = $this->productRepository->get('bundle1');
        $storeForCheck = $this->storeRepository->get($storeCodeForCheck);
        $result = $this->areBundleOptionsSalable->execute((int) $bundle->getId(), (int) $storeForCheck->getId());
        self::assertEquals($expectedResult, $result);
    }

    public static function executeDataProvider(): array
    {
        return [
            ['default', ['simple1'], 'default', true],
            ['default', ['simple3'], 'default', true],
            ['default', ['simple1', 'simple2'], 'default', false],
            ['default', ['simple1', 'simple2'], 'store2', true],
            ['store2', ['simple1', 'simple2', 'simple3'], 'store2', false],
            ['store2', ['simple1', 'simple2', 'simple3'], 'default', true],
            ['admin', ['simple1', 'simple2'], 'default', false],
            ['admin', ['simple1', 'simple2'], 'store2', false],
        ];
    }

    #[
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'website3'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website3.id$'], 'group3'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group3.id$', 'code' => 'store3'], 'store3'),
        DataFixture(ProductFixture::class, ['sku' => 'simple4', 'website_ids' => [1]], 's4'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s4.sku$'], 'link4'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link4$']], 'opt4'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle2', '_options' => ['$opt4$'], 'website_ids' => [1, '$website3.id']]
        ),
    ]
    public function testRequiredOptionWithChildNotAssignedToWebsiteIsNotSalable(): void
    {
        $bundle = $this->productRepository->get('bundle2');
        $store = $this->storeRepository->get('store3');
        $result = $this->areBundleOptionsSalable->execute((int) $bundle->getId(), (int) $store->getId());
        self::assertFalse($result);
    }

    #[
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'website4'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website4.id$'], 'group4'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group4.id$', 'code' => 'store4'], 'store4'),
        DataFixture(ProductFixture::class, ['sku' => 'simple5', 'website_ids' => [1, '$website4.id']], 's5'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s5.sku$'], 'link5'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link5$']], 'opt5'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle3', '_options' => ['$opt5$'], 'website_ids' => [1, '$website4.id']]
        ),
    ]
    public function testRequiredOptionWithChildAssignedToWebsiteIsSalable(): void
    {
        $bundle = $this->productRepository->get('bundle3');
        $store = $this->storeRepository->get('store4');
        $result = $this->areBundleOptionsSalable->execute((int) $bundle->getId(), (int) $store->getId());
        self::assertTrue($result);
    }
}
