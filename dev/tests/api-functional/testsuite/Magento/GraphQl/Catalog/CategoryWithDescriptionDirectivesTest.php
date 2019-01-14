<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for checking that category description directives are rendered correctly
 */
class CategoryWithDescriptionDirectivesTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testHtmlDirectivesRendered()
    {
        $categoryId = 333;
        $mediaFilePath = '/path/to/mediafile';
        /** @var StoreManagerInterface $storeManager */
        $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $storeBaseUrl = $storeManager->getStore()->getBaseUrl();

        /* Remove index.php from base URL */
        $storeBaseUrlParts = explode('/index.php', $storeBaseUrl);
        $storeBaseUrl = $storeBaseUrlParts[0];

        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        /** @var CategoryInterface $category */
        $category = $categoryRepository->get($categoryId);
        $category->setDescription('Test: {{media url="' . $mediaFilePath . '"}}');
        $categoryRepository->save($category);

        $query = <<<QUERY
{
  category(id: {$categoryId}) {
    description
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertNotContains('media url', $response['category']['description']);
        self::assertContains($storeBaseUrl, $response['category']['description']);
    }
}
