<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Update configurable product step.
 */
class UpdateConfigurableProductStep implements TestStepInterface
{
    /**
     * Catalog product edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog product attributes.
     *
     * @var CatalogProductAttribute
     */
    protected $deletedAttributes = [];

    /**
     * Old configurable product fixture.
     *
     * @var ConfigurableProduct
     */
    protected $initialProduct;

    /**
     * New configurable product fixture.
     *
     * @var ConfigurableProduct
     */
    protected $product;

    /**
     * Action type for attribute
     *
     * @var string
     */
    protected $attributeTypeAction = '';

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductEdit $catalogProductEdit
     * @param ConfigurableProduct $product
     * @param ConfigurableProduct $updatedProduct
     * @param string $attributeTypeAction
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        CatalogProductEdit $catalogProductEdit,
        ConfigurableProduct $product,
        ConfigurableProduct $updatedProduct,
        $attributeTypeAction = ''
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->initialProduct = $product;
        $this->product = $updatedProduct;
        $this->attributeTypeAction = $attributeTypeAction;
    }

    /**
     * Update configurable product.
     *
     * @return array
     */
    public function run()
    {
        $product = $this->prepareProduct($this->initialProduct, $this->product, $this->attributeTypeAction);
        $this->updateProduct($product);

        return ['product' => $product, 'deletedProductAttributes' => $this->deletedAttributes];
    }

    /**
     * Prepare new product for update.
     *
     * @param ConfigurableProduct $initialProduct
     * @param ConfigurableProduct $product
     * @param string $attributeTypeAction
     * @return ConfigurableProduct
     */
    protected function prepareProduct(
        ConfigurableProduct $initialProduct,
        ConfigurableProduct $product,
        $attributeTypeAction
    ) {
        if ($attributeTypeAction == 'deleteAll') {
            $this->deletedAttributes = $initialProduct->getDataFieldConfig('configurable_attributes_data')['source']
                ->getAttributes();
            return $product;
        }

        $dataProduct = $product->getData();
        $dataInitialProduct = $initialProduct->getData();
        $oldMatrix = [];

        if ($attributeTypeAction == 'deleteLast') {
            array_pop($dataInitialProduct['configurable_attributes_data']['attributes_data']);
            $attributes = $initialProduct->getDataFieldConfig('configurable_attributes_data')['source']
                ->getAttributes();
            $this->deletedAttributes[] = array_pop($attributes);
        }

        $attributesData = $dataInitialProduct['configurable_attributes_data']['attributes_data'];
        if ($attributeTypeAction == 'addOptions') {
            $oldMatrix = $dataInitialProduct['configurable_attributes_data']['matrix'];
            $this->addOptions($attributesData, $dataProduct['configurable_attributes_data']['attributes_data']);
        } else {
            $this->addAttributes($attributesData, $dataProduct['configurable_attributes_data']['attributes_data']);
        }

        $dataProduct['configurable_attributes_data'] = [
            'attributes_data' => $attributesData,
            'matrix' => $oldMatrix,
        ];

        if ($product->hasData('category_ids')) {
            $dataProduct['category_ids']['category'] = $product->getDataFieldConfig('category_ids')['source']
                ->getCategories()[0];
        }

        return $this->fixtureFactory->createByCode('configurableProduct', ['data' => $dataProduct]);
    }

    /**
     * Add options.
     *
     * @param array $attributes
     * @param array $data
     * @return void
     */
    protected function addOptions(array &$attributes, array $data)
    {
        foreach ($attributes as $key => $attribute) {
            if (isset($data[$key])) {
                $index = count($attribute['options']);
                foreach ($data[$key]['options'] as $newOption) {
                    $attributes[$key]['options']['option_key_' . $index] = $newOption;
                    $index++;
                }
            }
        }
    }

    /**
     * Add attributes.
     *
     * @param array $attributes
     * @param array $data
     * @return void
     */
    protected function addAttributes(array &$attributes, array $data)
    {
        $index = count($attributes);
        foreach ($data as $attribute) {
            $attributes['attribute_key_' . $index] = $attribute;
            $index++;
        }
    }

    /**
     * Update product.
     *
     * @param ConfigurableProduct $product
     * @return void
     */
    protected function updateProduct(ConfigurableProduct $product)
    {
        $productForm = $this->catalogProductEdit->getProductForm();
        $productForm->openTab('variations');
        $productForm->getTab('variations')->deleteAttributes();
        $this->catalogProductEdit->getProductForm()->fill($product);
    }
}
