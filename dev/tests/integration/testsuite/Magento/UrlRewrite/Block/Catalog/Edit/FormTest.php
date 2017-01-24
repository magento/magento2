<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Edit;

/**
 * Test for \Magento\UrlRewrite\Block\Catalog\Edit\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Get form instance
     *
     * @param array $args
     * @return \Magento\Framework\Data\Form
     */
    protected function _getFormInstance($args = [])
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $this->objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\UrlRewrite\Block\Catalog\Edit\Form */
        $block = $layout->createBlock(
            \Magento\UrlRewrite\Block\Catalog\Edit\Form::class,
            'block',
            ['data' => $args]
        );
        $block->setTemplate(null);
        $block->toHtml();
        return $block->getForm();
    }

    /**
     * Check _formPostInit set expected fields values
     *
     * @covers \Magento\UrlRewrite\Block\Catalog\Edit\Form::_formPostInit
     *
     * @dataProvider formPostInitDataProvider
     *
     * @param array $productData
     * @param array $categoryData
     * @param string $action
     * @param string $requestPath
     * @param string $targetPath
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     * @magentoAppIsolation enabled
     */
    public function testFormPostInitNew($productData, $categoryData, $action, $requestPath, $targetPath)
    {
        $args = [];
        if ($productData) {
            $args['product'] = $this->objectManager->create(
                \Magento\Catalog\Model\Product::class,
                ['data' => $productData]
            );
        }
        if ($categoryData) {
            $args['category'] = $this->objectManager->create(
                \Magento\Catalog\Model\Category::class,
                ['data' => $categoryData]
            );
        }
        $form = $this->_getFormInstance($args);
        $this->assertContains($action, $form->getAction());

        $this->assertEquals($requestPath, $form->getElement('request_path')->getValue());
        $this->assertEquals($targetPath, $form->getElement('target_path')->getValue());

        $this->assertTrue($form->getElement('target_path')->getData('disabled'));
    }

    /**
     * Test entity stores
     *
     * @dataProvider getEntityStoresDataProvider
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     *
     * @param array $productData
     * @param array $categoryData
     * @param array $expectedStores
     */
    public function testGetEntityStores($productData, $categoryData, $expectedStores)
    {
        $this->markTestSkipped('Skipped until MAGETWO-63018');
        $args = [];
        if ($productData) {
            $args['product'] = $this->objectManager->create(
                \Magento\Catalog\Model\Product::class,
                ['data' => $productData]
            );
        }
        if ($categoryData) {
            $args['category'] = $this->objectManager->create(
                \Magento\Catalog\Model\Category::class,
                ['data' => $categoryData]
            );
        }
        $form = $this->_getFormInstance($args);
        $this->assertEquals($expectedStores, $form->getElement('store_id')->getValues());
    }

    /**
     * Check exception is thrown when product does not associated with stores
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetEntityStoresProductStoresException()
    {
        $this->markTestSkipped('Skipped until MAGETWO-63018');
        $args = [
            'product' => $this->objectManager->create(
                \Magento\Catalog\Model\Product::class,
                ['data' => ['entity_id' => 1, 'name' => 'product1', 'url_key' => 'product2']]
            ),
        ];
        $form = $this->_getFormInstance($args);
        $this->assertEquals([], $form->getElement('store_id')->getValues());
        $this->assertEquals(
            'We can\'t set up a URL rewrite because the product you chose is not associated with a website.',
            $form->getElement('store_id')->getAfterElementHtml()
        );
    }

    /**
     * Check exception is thrown when product stores in intersection with category stores is empty
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     *
     */
    public function testGetEntityStoresProductCategoryStoresException()
    {
        $args = [
            'product' => $this->objectManager->create(
                \Magento\Catalog\Model\Product::class,
                ['data' => ['entity_id' => 1, 'name' => 'product1', 'url_key' => 'product1', 'store_ids' => [1]]]
            ),
            'category' => $this->objectManager->create(
                \Magento\Catalog\Model\Category::class,
                ['data' => ['entity_id' => 1, 'name' => 'category1', 'url_key' => 'category1', 'store_ids' => [3]]]
            ),
        ];
        $form = $this->_getFormInstance($args);
        $this->assertEquals([], $form->getElement('store_id')->getValues());
        $this->assertEquals(
            'We can\'t set up a URL rewrite because the product you chose is not associated with a website.',
            $form->getElement('store_id')->getAfterElementHtml()
        );
    }

    /**
     * Check exception is thrown when category does not associated with stores
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetEntityStoresCategoryStoresException()
    {
        $args = ['category' => $this->objectManager->create(
            \Magento\Catalog\Model\Category::class,
            ['data' => ['entity_id' => 1, 'name' => 'product1', 'url_key' => 'product', 'initial_setup_flag' => true]]
        )];
        $form = $this->_getFormInstance($args);
        $this->assertEquals([], $form->getElement('store_id')->getValues());
        $this->assertEquals(
            'Please assign a website to the selected category.',
            $form->getElement('store_id')->getAfterElementHtml()
        );
    }

    /**
     * Data provider for testing formPostInit
     * 1) Category selected
     * 2) Product selected
     * 3) Product with category selected
     *
     * @static
     * @return array
     */
    public static function formPostInitDataProvider()
    {
        return [
            [
                null,
                ['entity_id' => 3, 'level' => 2, 'parent_id' => 2, 'url_key' => 'category', 'store_id' => 1],
                'category/3',
                'category.html',
                'catalog/category/view/id/3',
            ],
            [
                ['entity_id' => 2, 'level' => 2,  'url_key' => 'product', 'store_id' => 1],
                null,
                'product/2',
                'product.html',
                'catalog/product/view/id/2'
            ],
            [
                ['entity_id' => 2, 'name' => 'product', 'url_key' => 'product', 'store_id' => 1],
                ['entity_id' => 3, 'parent_id' => 2, 'level' => 2, 'url_key' => 'category', 'store_id' => 1],
                'product/2/category/3',
                'category/product.html',
                'catalog/product/view/id/2/category/3'
            ]
        ];
    }

    /**
     * Entity stores data provider
     * 1) Category assigned to 1 store
     * 2) Product assigned to 1 store
     * 3) Product and category are assigned to same store
     *
     * @static
     * @return array
     */
    public static function getEntityStoresDataProvider()
    {
        return [
            [
                null,
                ['entity_id' => 3, 'store_ids' => [1]],
                [
                    ['label' => 'Main Website', 'value' => []],
                    [
                        'label' => '    Main Website Store',
                        'value' => [['label' => '    Default Store View', 'value' => 1]]
                    ]
                ],
            ],
            [
                ['entity_id' => 2, 'name' => 'product2', 'url_key' => 'product2', 'store_ids' => [1]],
                null,
                [
                    ['label' => 'Main Website', 'value' => []],
                    [
                        'label' => '    Main Website Store',
                        'value' => [['label' => '    Default Store View', 'value' => 1]]
                    ]
                ]
            ],
            [
                ['entity_id' => 2, 'name' => 'product2', 'url_key' => 'product2', 'store_ids' => [1]],
                ['entity_id' => 3, 'name' => 'product3', 'url_key' => 'product3', 'store_ids' => [1]],
                [
                    ['label' => 'Main Website', 'value' => []],
                    [
                        'label' => '    Main Website Store',
                        'value' => [['label' => '    Default Store View', 'value' => 1]]
                    ]
                ]
            ]
        ];
    }
}
