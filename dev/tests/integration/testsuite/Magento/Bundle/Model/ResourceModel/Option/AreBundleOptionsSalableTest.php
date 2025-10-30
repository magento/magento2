<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\TestCase;

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
     * @dataProvider executeDataProvider
     * @param string $storeCodeForChange
     * @param array $disabledChildren
     * @param string $storeCodeForCheck
     * @param bool $expectedResult
     * @return void
     */
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
}
