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
     * @magentoDataFixture Magento/Catalog/_files/products_in_categories.php
     * @dataProvider initializeCategoriesFromDataProvider
     * @param string $sku
     * @param array $categoryIds
     */
    public function testInitializeCategoriesFromData(string $sku, array $categoryIds): void
    {
        $productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productRepository->get($sku);
        $productData = $product->getData();
        $productData['category_ids'] = $categoryIds;

        $product = $this->initializationHelper->initializeFromData($product, $productData);
        $extensionAttributes = $product->getExtensionAttributes();
        $linkedCategoryIds = array_map(function (CategoryLinkInterface $categoryLink) {
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
            'assign category' => ['simple1', [3, 4]],
            'assign categories' => ['simple1', [3, 5, 6]],
            'unassign category' => ['simple2', [3, 6]],
            'unassign categories' => ['simple2', [3]],
            'update categories' => ['simple2', [3, 4, 6]],
            'change all categories' => ['simple2', [4]],
            'unassign all categories' => ['simple2', []],
            'assign new category' => ['simple3', [4]],
            'assign new categories' => ['simple3', [4, 5]],
        ];
    }
}
