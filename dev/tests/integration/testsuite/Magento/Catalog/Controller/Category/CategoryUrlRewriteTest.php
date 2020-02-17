<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Category;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Checks category availability on storefront by url rewrite
 *
 * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->config = $this->_objectManager->get(ScopeConfigInterface::class);
        $this->registry = $this->_objectManager->get(Registry::class);
        $this->categoryUrlSuffix = $this->config->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
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
    public function categoryRewriteProvider(): array
    {
        return [
            [
                'category_id' => 400,
                'url_path' => '/category-1%s',
            ],
            [
                'category_id' => 401,
                'url_path' => '/category-1/category-1-1%s',
            ],
            [
                'category_id' => 402,
                'url_path' => '/category-1/category-1-1/category-1-1-1%s',
            ],
        ];
    }
}
