<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Category\CategoryQuery;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting canonical url data from category
 */
class CategoryCanonicalUrlTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoConfigFixture default_store catalog/seo/category_canonical_tag 1
     */
    public function testCategoryWithCanonicalLinksMetaTagSettingsEnabled()
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->create(CategoryCollection::class);
        $categoryCollection->addFieldToFilter('name', 'Category 1.1.1');
        /** @var CategoryInterface $category */
        $category = $categoryCollection->getFirstItem();
        $categoryId = $category->getId();
        $query = <<<QUERY
    {
categoryList(filters: {ids: {in: ["$categoryId"]}}) {
    id
    name
   url_key
   url_suffix
   canonical_url
 }
}
QUERY;

        $response = $this->graphQlQuery($query);
        self::assertNotEmpty($response['categoryList'], 'Category list should not be empty');
        self::assertEquals('.html', $response['categoryList'][0]['url_suffix']);
        self::assertEquals(
            'category-1/category-1-1/category-1-1-1.html',
            $response['categoryList'][0]['canonical_url']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoConfigFixture default_store catalog/seo/category_canonical_tag 0
     */
    public function testCategoryWithCanonicalLinksMetaTagSettingsDisabled()
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->create(CategoryCollection::class);
        $categoryCollection->addFieldToFilter('name', 'Category 1.1');
        /** @var CategoryInterface $category */
        $category = $categoryCollection->getFirstItem();
        $categoryId = $category->getId();
        $query = <<<QUERY
    {
categoryList(filters: {ids: {in: ["$categoryId"]}}) {
    id
    name
   url_key
   canonical_url
 }
}
QUERY;

        $response = $this->graphQlQuery($query);
        self::assertNotEmpty($response['categoryList'], 'Category list should not be empty');
        self::assertNull(
            $response['categoryList'][0]['canonical_url']
        );
        self::assertEquals('category-1-1', $response['categoryList'][0]['url_key']);
    }
}
