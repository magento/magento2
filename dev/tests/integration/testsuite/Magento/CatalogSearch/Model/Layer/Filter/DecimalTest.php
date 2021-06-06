<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Layer\Filter;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Layer\Category as LayerCategory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CatalogSearch\Model\Layer\Filter\Decimal.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_weight_filterable.php
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/Price/_files/products_base.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DecimalTest extends TestCase
{
    /**
     * @var Decimal
     */
    protected $_model;

    /**
     * @var mixed
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
        $category = $categoryRepository->get(4);

        /** @var LayerCategory $layer */
        $layer = $objectManager->create(
            LayerCategory::class,
            ['data' => ['current_category' => $category]]
        );
        /** @var ProductAttributeInterface $attribute */
        $attribute = $objectManager->get(ProductAttributeInterface::class);
        $attribute->loadByCode('catalog_product', 'weight');
        $this->request = $objectManager->get(Request::class);
        $this->_model = $objectManager->create(Decimal::class, ['layer' => $layer]);
        $this->_model->setAttributeModel($attribute);
        $this->_model->setRequestVar('decimal');
    }

    /**
     * @return void
     */
    public function testApplyNothing()
    {
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
        $this->_model->apply($this->request);
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
    }

    /**
     * @return void
     */
    public function testApplyInvalid()
    {
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
        $this->request->setParam('decimal', 'non-decimal');
        $this->_model->apply($this->request);

        $filters = $this->_model->getLayer()->getState()->getFilters();
        $this->assertArrayHasKey(0, $filters);
        $this->assertEquals(
            '<span class="price">$0.00</span> - <span class="price">$0.00</span>',
            (string)$filters[0]->getLabel()
        );
    }

    /**
     * @return void
     */
    public function testApply()
    {
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
        $this->request->setParam('decimal', '1-100');
        $this->_model->apply($this->request);

        $filters = $this->_model->getLayer()->getState()->getFilters();
        $this->assertArrayHasKey(0, $filters);
        $this->assertEquals(
            '<span class="price">$1.00</span> - <span class="price">$99.99</span>',
            (string)$filters[0]->getLabel()
        );
    }
}
