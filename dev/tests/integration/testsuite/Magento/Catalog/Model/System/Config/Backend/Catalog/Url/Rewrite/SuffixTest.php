<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\Cache\Type\Collection;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\Storage\DbStorage;
use PHPUnit\Framework\TestCase;

/**
 * Class checks url suffix config save behaviour
 *
 * @see \Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuffixTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Suffix */
    private $model;

    /** @var DbStorage */
    private $urlFinder;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var TypeListInterface */
    private $typeList;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var int */
    private $defaultStoreId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Suffix::class);
        $this->urlFinder = $this->objectManager->get(DbStorage::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->typeList = $this->objectManager->get(TypeListInterface::class);
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->defaultStoreId = (int)$this->storeManager->getStore('default')->getId();
    }

    /**
     * @return void
     */
    public function testSaveWithError(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage((string)__('Anchor symbol (#) is not supported in url rewrite suffix.'));
        $this->model->setValue('.html#');
        $this->model->beforeSave();
    }

    /**
     * @dataProvider wrongValuesProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @param array $data
     * @return void
     */
    public function testSaveWithWrongData(array $data): void
    {
        $productId = (int)$this->productRepository->get('simple2')->getId();
        $this->model->addData($data);
        $this->model->afterSave();
        $this->assertRewrite(
            $this->scopeConfig->getValue(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX),
            [
                'entity_type' => ProductUrlRewriteGenerator::ENTITY_TYPE,
                'entity_id' => $productId,
                'store_id' => $this->defaultStoreId,
            ]
        );
    }

    /**
     * @return array
     */
    public function wrongValuesProvider(): array
    {
        return [
            'with_wrong_path' => [
                ['path' => 'wrong_path', 'value' => 'some_test_value'],
            ],
            'with_null_value' => [
                ['path' => ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX, 'value' => null],
            ],
        ];
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/product_multistore_different_short_description.php
     *
     * @return void
     */
    public function testSaveInStoreScope(): void
    {
        $productId = $this->productRepository->get('simple-different-short-description')->getId();
        $newSuffix = 'some_test_value_for_store';
        $storeId = $this->storeManager->getStore('fixturestore')->getId();
        $this->model->addData([
            'path' => ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            'value' => $newSuffix,
            'scope' => ScopeInterface::SCOPE_STORES,
            'scope_id' => $storeId,
        ]);
        $this->model->afterSave();
        $this->assertRewrite(
            $this->scopeConfig->getValue(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX),
            [
                'entity_type' => ProductUrlRewriteGenerator::ENTITY_TYPE,
                'entity_id' => $productId,
                'store_id' => $this->defaultStoreId,
            ]
        );
        $this->assertRewrite(
            $newSuffix,
            [
                'entity_type' => ProductUrlRewriteGenerator::ENTITY_TYPE,
                'entity_id' => $productId,
                'store_id' => $storeId,
            ]
        );
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     *
     * @return void
     */
    public function testSaveInWebsiteScope(): void
    {
        $productId = (int)$this->productRepository->get('simple-on-two-websites')->getId();
        $newSuffix = 'some_test_value_for_website';
        $website = $this->storeManager->getWebsite('test');
        $this->model->addData([
            'path' => ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            'value' => $newSuffix,
            'scope' => ScopeInterface::SCOPE_WEBSITES,
            'scope_id' => $website->getId(),
        ]);
        $this->model->afterSave();
        $this->assertRewrite(
            $this->scopeConfig->getValue(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX),
            [
                'entity_type' => ProductUrlRewriteGenerator::ENTITY_TYPE,
                'entity_id' => $productId,
                'store_id' => $this->defaultStoreId,
            ]
        );
        $this->assertRewrite(
            $newSuffix,
            [
                'entity_type' => ProductUrlRewriteGenerator::ENTITY_TYPE,
                'entity_id' => $productId,
                'store_id' => $website->getStoreIds(),
            ]
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @magentoConfigFixture default_store catalog/seo/product_url_suffix .html_default
     *
     * @return void
     */
    public function testSaveDefaultScopeWithOverrideStoreScope(): void
    {
        $productId = (int)$this->productRepository->get('simple2')->getId();
        $newSuffix = 'some_test_value';
        $this->model->addData([
            'path' => ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            'value' => $newSuffix,
        ]);
        $this->model->afterSave();
        $this->assertRewrite(
            '.html_default',
            [
                'entity_type' => ProductUrlRewriteGenerator::ENTITY_TYPE,
                'entity_id' => $productId,
                'store_id' => $this->defaultStoreId,
            ]
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     *
     * @return void
     */
    public function testSaveCategorySuffix(): void
    {
        $this->model->addData(['path' => CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, 'value' => null]);
        $this->model->afterSave();
        $this->assertRewrite('.html', ['entity_type' => CategoryUrlRewriteGenerator::ENTITY_TYPE]);
        $this->checkIsCacheInvalidated();
    }

    /**
     * @return void
     */
    public function testDeleteCategorySuffix(): void
    {
        $this->model->addData(
            ['path' => CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, 'value' => 'test_value']
        );
        $this->model->afterDeleteCommit();
        $this->checkIsCacheInvalidated();
    }

    /**
     * Check that provided cache types are invalidated
     *
     * @param array $cacheTypes
     * @return void
     */
    private function checkIsCacheInvalidated(
        array $cacheTypes = [Block::TYPE_IDENTIFIER, Collection::TYPE_IDENTIFIER]
    ): void {
        $types = $this->typeList->getTypes();

        foreach ($cacheTypes as $type) {
            $this->assertNotNull($types[$type]);
            $this->assertEquals(0, $types[$type]->getStatus());
        }
    }

    /**
     * Assert url rewrite rewrite
     *
     * @param string $expectedSuffix
     * @param array $data
     * @return void
     */
    private function assertRewrite(string $expectedSuffix, array $data): void
    {
        $rewrite = $this->urlFinder->findOneByData($data);
        $this->assertNotNull($rewrite);
        $this->assertTrue(
            substr($rewrite->getRequestPath(), -strlen($expectedSuffix)) === $expectedSuffix,
            'The url rewrite suffix does not match expected value'
        );
    }
}
