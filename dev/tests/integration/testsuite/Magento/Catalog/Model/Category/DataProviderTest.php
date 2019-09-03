<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate;

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
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->dataProvider = $this->createDataProvider();
        $this->registry = $objectManager->get(Registry::class);
        $this->categoryFactory = $objectManager->get(CategoryFactory::class);
    }

    /**
     * @return void
     */
    public function testGetMetaRequiredAttributes()
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
     * Check that custom layout update file attribute is processed correctly.
     *
     * @return void
     */
    public function testCustomLayoutFileAttribute(): void
    {
        //File has value
        /** @var Category $category */
        $category = $this->categoryFactory->create();
        $category->load($id = 2);
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
}
