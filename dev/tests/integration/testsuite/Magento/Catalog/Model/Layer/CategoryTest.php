<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

/**
 * Test class for \Magento\Catalog\Model\Layer.
 *
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\Category
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Layer\Category::class
        );
        $this->_model->setCurrentCategory(4);
    }

    public function testGetStateKey()
    {
        $this->assertEquals('STORE_1_CAT_4_CUSTGROUP_0', $this->_model->getStateKey());
    }

    public function testGetProductCollection()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_model->getProductCollection();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, $collection);
        $this->assertEquals(2, $collection->count());
        $this->assertSame($collection, $this->_model->getProductCollection());
    }

    public function testApply()
    {
        $this->_model->getState()->addFilter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Layer\Filter\Item::class,
                [
                    'data' => [
                        'filter' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                            \Magento\Catalog\Model\Layer\Filter\Category::class,
                            ['layer' => $this->_model]
                        ),
                        'value' => 'expected-value-string',
                    ]
                ]
            )
        )->addFilter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Layer\Filter\Item::class,
                [
                    'data' => [
                        'filter' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                            \Magento\Catalog\Model\Layer\Filter\Decimal::class,
                            ['layer' => $this->_model]
                        ),
                        'value' => 1234,
                    ]
                ]
            )
        );

        $this->_model->apply();
        $this->assertEquals(
            'STORE_1_CAT_4_CUSTGROUP_0_cat_expected-value-string_decimal_1234',
            $this->_model->getStateKey()
        );

        $this->_model->apply();
        $this->assertEquals(
            'STORE_1_CAT_4_CUSTGROUP_0_cat_expected-value-string_decimal_1234_cat_expected-value-string_decimal_1234',
            $this->_model->getStateKey()
        );
    }

    public function testGetSetCurrentCategory()
    {
        $existingCategory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $existingCategory->load(5);

        /* Category object */
        /** @var $model \Magento\Catalog\Model\Layer */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Layer\Category::class
        );
        $model->setCurrentCategory($existingCategory);
        $this->assertSame($existingCategory, $model->getCurrentCategory());

        /* Category id */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Layer\Category::class
        );
        $model->setCurrentCategory(3);
        $actualCategory = $model->getCurrentCategory();
        $this->assertInstanceOf(\Magento\Catalog\Model\Category::class, $actualCategory);
        $this->assertEquals(3, $actualCategory->getId());
        $this->assertSame($actualCategory, $model->getCurrentCategory());

        /* Category in registry */
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_category', $existingCategory);
        try {
            $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Layer\Category::class
            );
            $this->assertSame($existingCategory, $model->getCurrentCategory());
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_category');
            $this->assertSame($existingCategory, $model->getCurrentCategory());
        } catch (\Exception $e) {
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_category');
            throw $e;
        }

        try {
            $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Layer\Category::class
            );
            $model->setCurrentCategory(new \Magento\Framework\DataObject());
            $this->fail('Assign category of invalid class.');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        }

        try {
            $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Layer\Category::class
            );
            $model->setCurrentCategory(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                    \Magento\Catalog\Model\Category::class
                )
            );
            $this->fail('Assign category with invalid id.');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        }
    }

    public function testGetCurrentStore()
    {
        $this->assertSame(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore(),
            $this->_model->getCurrentStore()
        );
    }

    public function testGetState()
    {
        $state = $this->_model->getState();
        $this->assertInstanceOf(\Magento\Catalog\Model\Layer\State::class, $state);
        $this->assertSame($state, $this->_model->getState());

        $state = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Layer\State::class
        );
        $this->_model->setState($state);
        // $this->_model->setData('state', state);
        $this->assertSame($state, $this->_model->getState());
    }
}
