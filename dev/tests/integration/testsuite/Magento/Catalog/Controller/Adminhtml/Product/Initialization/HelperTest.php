<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Helper
     */
    private $initializationHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->initializationHelper = Bootstrap::getObjectManager()->create(Helper::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @dataProvider initializeCategoriesFromDataProvider
     * @param string $sku
     * @param array $categoryIds
     */
    public function testInitializeCategoriesFromData(string $sku, array $categoryIds): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productRepository->get($sku);
        $productData = $product->getData();
        $productData['category_ids'] = $categoryIds;

        $product = $this->initializationHelper->initializeFromData($product, $productData);
        $extensionAttributes = $product->getExtensionAttributes();
        $linkedCategoryIds = array_map(function(CategoryLinkInterface $categoryLink) {
            return $categoryLink->getCategoryId();
        }, (array) $extensionAttributes->getCategoryLinks());
        $this->assertEquals($categoryIds, $linkedCategoryIds);
    }

    /**
     * @return array
     */
    public function initializeCategoriesFromDataProvider(): array
    {
        return [
            'assign categories' => [
                'simple',
                [2, 3, 4, 11, 12, 13],
            ],
            'unassign categories' => [
                'simple-4',
                [11, 12],
            ],
            'update categories' => [
                'simple-3',
                [10, 12, 13],
            ],
            'unassign all categories' => [
                'simple-3',
                [],
            ],
            'assign new category' => [
                'simple2',
                [11],
            ],
            'assign new categories' => [
                'simple2',
                [11, 13],
            ],
        ];
    }
}
