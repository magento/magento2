<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Request;
use Magento\TestFramework\Response;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Checks category availability on storefront by url rewrite
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation enabled
 */
class CategoryUrlRewriteTest extends AbstractController
{
    /** @var Registry */
    private $registry;

    /** @var ScopeConfigInterface */
    private $config;

    /** @var string */
    private $categoryUrlSuffix;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CatalogSession */
    private $catalogSession;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->_objectManager->get(ScopeConfigInterface::class);
        $this->registry = $this->_objectManager->get(Registry::class);
        $this->categoryUrlSuffix = $this->config->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->catalogSession = $this->_objectManager->get(CatalogSession::class);
        $this->executeInStoreContext = $this->_objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @dataProvider categoryRewriteProvider
     * @param int $categoryId
     * @param string $urlPath
     * @return void
     */
    public function testCategoryUrlRewrite(int $categoryId, string $urlPath): void
    {
        $this->dispatch(sprintf($urlPath, $this->categoryUrlSuffix));
        $currentCategory = $this->registry->registry('current_category');
        $response = $this->getResponse();
        $this->assertEquals(
            Http::STATUS_CODE_200,
            $response->getHttpResponseCode(),
            'Response code does not match expected value'
        );
        $this->assertNotNull($currentCategory);
        $this->assertEquals($categoryId, $currentCategory->getId());
    }

    /**
     * @return array
     */
    public static function categoryRewriteProvider(): array
    {
        return [
            [
                'categoryId' => 400,
                'urlPath' => '/category-1%s',
            ],
            [
                'categoryId' => 401,
                'urlPath' => '/category-1/category-1-1%s',
            ],
            [
                'categoryId' => 402,
                'urlPath' => '/category-1/category-1-1/category-1-1-1%s',
            ],
        ];
    }

    /**
     * Test category url on different store view
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @return void
     */
    public function testCategoryUrlOnStoreView(): void
    {
        $id = 333;
        $secondStoreUrlKey = 'category-1-second';
        $currentStore = $this->storeManager->getStore();
        $secondStore = $this->storeManager->getStore('test');
        $this->executeInStoreContext->execute(
            $secondStore,
            [$this, 'updateCategoryUrlKey'],
            $id,
            (int)$secondStore->getId(),
            $secondStoreUrlKey
        );
        $url = sprintf('/' . $secondStoreUrlKey . '%s', $this->categoryUrlSuffix);
        $this->executeInStoreContext->execute($secondStore, [$this, 'dispatch'], $url);
        $this->assertCategoryIsVisible();
        $this->assertEquals(
            $secondStoreUrlKey,
            $this->categoryRepository->get($id, (int)$secondStore->getId())->getUrlKey(),
            'Wrong category is registered'
        );
        $this->cleanUpCachedObjects();
        $defaultStoreUrlKey = $this->categoryRepository->get($id, $currentStore->getId())->getUrlKey();
        $this->dispatch(sprintf($defaultStoreUrlKey . '%s', $this->categoryUrlSuffix));
        $this->assertCategoryIsVisible();
    }

    /**
     * Assert that category is available in storefront
     *
     * @return void
     */
    private function assertCategoryIsVisible(): void
    {
        $this->assertEquals(
            Response::STATUS_CODE_200,
            $this->getResponse()->getHttpResponseCode(),
            'Wrong response code is returned'
        );
        $this->assertNotNull((int)$this->catalogSession->getData('last_viewed_category_id'));
    }

    /**
     * Clean up cached objects
     *
     * @return void
     */
    private function cleanUpCachedObjects(): void
    {
        $this->catalogSession->clearStorage();
        $this->registry->unregister('current_category');
        $this->registry->unregister('category');
        $this->_objectManager->removeSharedInstance(Request::class);
        $this->_objectManager->removeSharedInstance(Response::class);
        $this->_objectManager->removeSharedInstance(Resolver::class);
        $this->_objectManager->removeSharedInstance(Category::class);
        $this->_objectManager->removeSharedInstance('categoryFilterList');
        $this->_response = null;
        $this->_request = null;
    }

    /**
     * Update category url key
     *
     * @param int $id
     * @param int $storeId
     * @param string $categoryUrlKey
     * @return void
     */
    public function updateCategoryUrlKey(int $id, int $storeId, string $categoryUrlKey): void
    {
        $category = $this->categoryRepository->get($id, $storeId);
        $category->setUrlKey($categoryUrlKey);
        $this->categoryRepository->save($category);
    }
}
