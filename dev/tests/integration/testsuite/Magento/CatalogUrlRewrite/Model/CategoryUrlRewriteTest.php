<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Class for category url rewrites tests
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CategoryUrlRewriteTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryFactory */
    private $categoryFactory;

    /** @var UrlRewriteCollectionFactory */
    private $urlRewriteCollectionFactory;

    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var CategoryResource */
    private $categoryResource;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFactory = $this->objectManager->get(CategoryFactory::class);
        $this->urlRewriteCollectionFactory = $this->objectManager->get(UrlRewriteCollectionFactory::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepository::class);
        $this->categoryResource = $this->objectManager->get(CategoryResource::class);
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     * @dataProvider categoryProvider
     * @param array $data
     * @return void
     */
    public function testUrlRewriteOnCategorySave(array $data): void
    {
        $categoryModel = $this->categoryFactory->create();
        $categoryModel->isObjectNew(true);
        $categoryModel->setData($data['data']);
        $this->categoryResource->save($categoryModel);
        $this->assertNotNull($categoryModel->getId(), 'The category was not created');
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $urlRewriteCollection->addFieldToFilter(UrlRewrite::ENTITY_ID, ['eq' => $categoryModel->getId()])
            ->addFieldToFilter(UrlRewrite::ENTITY_TYPE, ['eq' => DataCategoryUrlRewriteDatabaseMap::ENTITY_TYPE]);

        foreach ($urlRewriteCollection as $item) {
            foreach ($data['expected_data'] as $field => $expectedItem) {
                $this->assertEquals(
                    sprintf($expectedItem, $categoryModel->getId()),
                    $item[$field],
                    'The expected data does not match actual value'
                );
            }
        }
    }

    /**
     * @return array
     */
    public function categoryProvider(): array
    {
        return [
            'without_url_key' => [
                [
                    'data' => [
                        'name' => 'Test Category',
                        'attribute_set_id' => '3',
                        'parent_id' => 2,
                        'path' => '1/2',
                        'is_active' => true,
                    ],
                    'expected_data' => [
                        'request_path' => 'test-category.html',
                        'target_path' => 'catalog/category/view/id/%s',
                    ],
                ],
            ],
            'subcategory_without_url_key' => [
                [
                    'data' => [
                        'name' => 'Test Sub Category',
                        'attribute_set_id' => '3',
                        'parent_id' => 444,
                        'path' => '1/2/444',
                        'is_active' => true,
                    ],
                    'expected_data' => [
                        'request_path' => 'category-1/test-sub-category.html',
                        'target_path' => 'catalog/category/view/id/%s',
                    ],
                ],
            ],
        ];
    }
}
