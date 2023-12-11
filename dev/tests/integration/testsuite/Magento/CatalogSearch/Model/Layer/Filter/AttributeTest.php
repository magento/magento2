<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Layer\Filter;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Layer\Category as LayerCategory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CatalogSearch\Model\Layer\Filter\Attribute.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
 */
class AttributeTest extends TestCase
{
    /**
     * @var Attribute
     */
    protected $_model;

    /**
     * @var int
     */
    protected $_attributeOptionId;

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
        /** @var ProductAttributeInterface $attribute */
        $attribute = $objectManager->get(ProductAttributeInterface::class);
        $attribute->loadByCode('catalog_product', 'attribute_with_option');
        foreach ($attribute->getSource()->getAllOptions() as $optionInfo) {
            if ($optionInfo['label'] == 'Option Label') {
                $this->_attributeOptionId = $optionInfo['value'];
                break;
            }
        }

        /** @var LayerCategory $layer */
        $layer = $objectManager->get(LayerCategory::class);
        $this->request = $objectManager->get(Request::class);
        $this->_model = $objectManager->create(Attribute::class, ['layer' => $layer]);
        $this->_model->setAttributeModel($attribute);
        $this->_model->setRequestVar('attribute');
    }

    /**
     * @return void
     */
    public function testOptionIdNotEmpty()
    {
        $this->assertNotEmpty($this->_attributeOptionId, 'Fixture attribute option id.'); // just in case
    }

    /**
     * @return void
     */
    public function testApplyInvalid()
    {
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
        $this->request->setParam('attribute', []);
        $this->_model->apply($this->request);

        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
    }

    /**
     * @return void
     */
    public function testApply()
    {
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
        $this->request->setParam('attribute', $this->_attributeOptionId);
        $this->_model->apply($this->request);

        $this->assertNotEmpty($this->_model->getLayer()->getState()->getFilters());
    }

    /**
     * @return void
     */
    public function testGetItemsWithApply()
    {
        $this->request->setParam('attribute', $this->_attributeOptionId);
        $this->_model->apply($this->request);
        $items = $this->_model->getItems();

        $this->assertIsArray($items);
        $this->assertEmpty($items);
    }
}
