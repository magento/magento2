<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CategoriesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Categories
     */
    private $object;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        /** @var $store \Magento\Store\Model\Store */
        $store = $objectManager->create(\Magento\Store\Model\Store::class);
        $store->load('admin');
        $registry->register('current_store', $store);
        $this->object = $objectManager->create(Categories::class);
    }

    public function testModifyMeta()
    {
        $inputMeta = include __DIR__ . '/_files/input_meta_for_categories.php';
        $meta = $this->object->modifyMeta($inputMeta);
        $expectedCategories = include __DIR__ . '/_files/expected_categories.php';
        $categoriesElement = $meta['product-details']['children']['container_category_ids']['children']['category_ids'];
        $this->assertEquals($expectedCategories, $categoriesElement['arguments']['data']['config']['options']);
    }
}
