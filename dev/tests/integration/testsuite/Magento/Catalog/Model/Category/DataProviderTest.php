<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryLayoutUpdateManager
     */
    private $fakeFiles;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Create subject instance.
     *
     * @return DataProvider
     */
    private function createDataProvider(): DataProvider
    {
        return Bootstrap::getObjectManager()->create(
            DataProvider::class,
            [
                'name' => 'category_form_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id'
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->configure([
            'preferences' => [
                \Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager::class
                => \Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager::class
            ]
        ]);
        parent::setUp();
        $this->dataProvider = $this->createDataProvider();
        $this->registry = $objectManager->get(Registry::class);
        $this->categoryFactory = $objectManager->get(CategoryFactory::class);
        $this->fakeFiles = $objectManager->get(CategoryLayoutUpdateManager::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @return void
     */
    public function testGetMetaRequiredAttributes(): void
    {
        $requiredAttributes = [
            'general' => ['name'],
            'display_settings' => ['available_sort_by', 'default_sort_by'],
        ];
        $meta = $this->dataProvider->getMeta();
        $this->assertArrayHasKey('url_key', $meta['search_engine_optimization']['children']);
        foreach ($requiredAttributes as $scope => $attributes) {
            foreach ($attributes as $attribute) {
                $this->assertArrayHasKey($attribute, $meta[$scope]['children']);
                $data = $meta[$scope]['children'][$attribute];
                $this->assertTrue($data['arguments']['data']['config']['validation']['required-entry']);
            }
        }
    }

    /**
     * Check that deprecated custom layout attribute is hidden.
     *
     * @return void
     */
    public function testOldCustomLayoutInvisible(): void
    {
        //Testing a category without layout xml
        /** @var Category $category */
        $category = $this->categoryFactory->create();
        $category->load(2);
        $this->registry->register('category', $category);

        $meta = $this->dataProvider->getMeta();
        $this->assertArrayHasKey('design', $meta);
        $this->assertArrayHasKey('children', $meta['design']);
        $this->assertArrayHasKey('custom_layout_update', $meta['design']['children']);
        $this->assertArrayHasKey('arguments', $meta['design']['children']['custom_layout_update']);
        $this->assertArrayHasKey('data', $meta['design']['children']['custom_layout_update']['arguments']);
        $this->assertArrayHasKey(
            'config',
            $meta['design']['children']['custom_layout_update']['arguments']['data']
        );
        $config = $meta['design']['children']['custom_layout_update']['arguments']['data']['config'];
        $this->assertTrue($config['visible'] === false);
    }

    /**
     * Check that custom layout update file attribute is processed correctly.
     *
     * @return void
     */
    public function testCustomLayoutFileAttribute(): void
    {
        //File has value
        /** @var Category $category */
        $category = $this->categoryFactory->create();
        $id = 2;
        $category->load($id);
        $category->setData('custom_layout_update', null);
        $category->setData('custom_layout_update_file', $file = 'test-file');
        $this->registry->register('category', $category);
        $data = $this->dataProvider->getData();
        $this->assertEquals($file, $data[$id]['custom_layout_update_file']);

        //File has no value, the deprecated attribute does.
        $this->dataProvider = $this->createDataProvider();
        $category->setData('custom_layout_update', $deprecated = 'test-deprecated');
        $category->setData('custom_layout_update_file', null);
        $data = $this->dataProvider->getData();
        $this->assertEquals($deprecated, $data[$id]['custom_layout_update']);
        $this->assertEquals(LayoutUpdate::VALUE_USE_UPDATE_XML, $data[$id]['custom_layout_update_file']);
    }

    /**
     * Extract custom layout update file attribute's options from metadata.
     *
     * @param array $meta
     * @return array
     */
    private function extractCustomLayoutOptions(array $meta): array
    {
        $this->assertArrayHasKey('design', $meta);
        $this->assertArrayHasKey('children', $meta['design']);
        $this->assertArrayHasKey('custom_layout_update_file', $meta['design']['children']);
        $this->assertArrayHasKey('arguments', $meta['design']['children']['custom_layout_update_file']);
        $this->assertArrayHasKey('data', $meta['design']['children']['custom_layout_update_file']['arguments']);
        $this->assertArrayHasKey(
            'config',
            $meta['design']['children']['custom_layout_update_file']['arguments']['data']
        );
        $this->assertArrayHasKey(
            'options',
            $meta['design']['children']['custom_layout_update_file']['arguments']['data']['config']
        );

        return $meta['design']['children']['custom_layout_update_file']['arguments']['data']['config']['options'];
    }

    /**
     * Check that proper options are returned for a category.
     *
     * @return void
     */
    public function testCustomLayoutMeta(): void
    {
        //Testing a category without layout xml
        /** @var Category $category */
        $category = $this->categoryFactory->create();
        $category->load(2);
        $this->fakeFiles->setCategoryFakeFiles((int)$category->getId(), ['test1', 'test2']);
        $this->registry->register('category', $category);

        $meta = $this->dataProvider->getMeta();
        $list = $this->extractCustomLayoutOptions($meta);
        $expectedList = [
            [
                'label' => 'No update',
                'value' => \Magento\Catalog\Model\Attribute\Backend\AbstractLayoutUpdate::VALUE_NO_UPDATE,
                '__disableTmpl' => true
            ],
            ['label' => 'test1', 'value' => 'test1', '__disableTmpl' => true],
            ['label' => 'test2', 'value' => 'test2', '__disableTmpl' => true]
        ];
        sort($expectedList);
        sort($list);
        $this->assertEquals($expectedList, $list);

        //Product with old layout xml
        $category->setCustomAttribute('custom_layout_update', 'test');
        $this->fakeFiles->setCategoryFakeFiles((int)$category->getId(), ['test3']);

        $meta = $this->dataProvider->getMeta();
        $expectedList = [
            [
                'label' => 'No update',
                'value' => \Magento\Catalog\Model\Attribute\Backend\AbstractLayoutUpdate::VALUE_NO_UPDATE,
                '__disableTmpl' => true
            ],
            [
                'label' => 'Use existing',
                'value' => LayoutUpdate::VALUE_USE_UPDATE_XML,
                '__disableTmpl' => true
            ],
            ['label' => 'test3', 'value' => 'test3', '__disableTmpl' => true],
        ];
        $list = $this->extractCustomLayoutOptions($meta);
        sort($expectedList);
        sort($list);
        $this->assertEquals($expectedList, $list);
    }

    /**
     * Check if existing category page layout will remain unaffected by category page layout default value setting
     *
     * @return void
     */
    public function testExistingCategoryLayoutUnaffectedByDefaults(): void
    {
        /** @var Category $category */
        $category = $this->categoryFactory->create();
        $category->load(2);

        $this->registry->register('category', $category);
        $meta = $this->dataProvider->getMeta();
        $categoryPageLayout = $meta["design"]["children"]["page_layout"]["arguments"]["data"]["config"]["default"];
        $this->registry->unregister('category');

        $this->assertNull($categoryPageLayout);
    }

    /**
     * Check if category page layout default value setting will apply to the new category during it's creation
     *
     * @throws NoSuchEntityException
     */
    public function testNewCategoryLayoutMatchesDefault(): void
    {
        $categoryDefaultPageLayout = $this->scopeConfig->getValue(
            'web/default_layouts/default_category_layout',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        /** @var Category $category */
        $category = $this->categoryFactory->create();
        $category->setName('Net Test Category');

        $this->registry->register('category', $category);
        $meta = $this->dataProvider->getMeta();
        $categoryPageLayout = $meta["design"]["children"]["page_layout"]["arguments"]["data"]["config"]["default"];
        $this->registry->unregister('category');

        $this->assertEquals($categoryDefaultPageLayout, $categoryPageLayout);
    }
}
