<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Handler\ConfigurableProduct;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as ProductCurl;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct\ConfigurableAttributesData;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Create new configurable product via curl.
 */
class Curl extends ProductCurl implements ConfigurableProductInterface
{
    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        parent::__construct($configuration, $eventManager);

        $this->mappingData += [
            'include' => [
                'Yes' => 1,
                'No' => 0,
            ]
        ];
    }

    /**
     * Prepare POST data for creating product request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    public function prepareData(FixtureInterface $fixture)
    {
        $data = parent::prepareData($fixture);

        /** @var ConfigurableAttributesData $configurableAttributesData */
        $configurableAttributesData = $fixture->getDataFieldConfig('configurable_attributes_data')['source'];
        $attributeSetId = $data['product']['attribute_set_id'];

        $data['product']['configurable_attributes_data'] = $this->prepareAttributesData($configurableAttributesData);
        $data['product']['configurable-matrix-serialized'] = json_encode($this->prepareConfigurableMatrix($fixture));
        $data['attributes'] = $this->prepareAttributes($configurableAttributesData);
        $data['new-variations-attribute-set-id'] = $attributeSetId;
        $data['product']['associated_product_ids_serialized'] =
            json_encode($this->prepareAssociatedProductIds($configurableAttributesData));

        return $this->replaceMappingData($data);
    }

    /**
     * Preparation of attribute set data.
     *
     * @return void
     */
    protected function prepareAttributeSet()
    {
        /** @var ConfigurableAttributesData $configurableAttributesData */
        $configurableAttributesData = $this->fixture->getDataFieldConfig('configurable_attributes_data')['source'];
        $attributeSet = $configurableAttributesData->getAttributeSet();

        if ($attributeSet) {
            $this->fields['product']['attribute_set_id'] = $attributeSet->getAttributeSetId();
        } elseif ($this->fixture->hasData('attribute_set_id')) {
            $this->fields['product']['attribute_set_id'] = $this->fixture
                ->getDataFieldConfig('attribute_set_id')['source']
                ->getAttributeSet()
                ->getAttributeSetId();
        } else {
            $this->fields['product']['attribute_set_id'] = 'Default';
        }
    }

    /**
     * Preparing attribute data.
     *
     * @param ConfigurableAttributesData $configurableAttributesData
     * @return array
     */
    protected function prepareAttributesData(ConfigurableAttributesData $configurableAttributesData)
    {
        $optionFields = [
            'pricing_value',
            'include',
        ];
        $result = [];

        foreach ($configurableAttributesData->getAttributesData() as $attribute) {
            $attributeId = isset($attribute['attribute_id']) ? $attribute['attribute_id'] : null;
            $dataOptions = [];

            foreach ($attribute['options'] as $option) {
                $optionId = isset($option['id']) ? $option['id'] : null;

                $dataOption = array_intersect_key($option, array_flip($optionFields));
                $dataOption['value_index'] = $optionId;

                $dataOptions[$optionId] = $dataOption;
            }

            $result[$attributeId] = [
                'code' => $attribute['attribute_code'],
                'attribute_id' => $attributeId,
                'label' => $attribute['frontend_label'],
                'values' => $dataOptions,
            ];
        }

        return $result;
    }

    /**
     * Preparing matrix data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareConfigurableMatrix(FixtureInterface $product)
    {
        /** @var ConfigurableAttributesData $configurableAttributesData */
        $configurableAttributesData = $product->getDataFieldConfig('configurable_attributes_data')['source'];
        $attributesData = $configurableAttributesData->getAttributesData();
        $assignedProducts = $configurableAttributesData->getProducts();
        $matrixData = $product->getConfigurableAttributesData()['matrix'];
        $result = [];

        foreach ($matrixData as $variationKey => $variation) {
            // For assigned products doesn't send data about them
            if (isset($assignedProducts[$variationKey])) {
                continue;
            }

            $compositeKeys = explode(' ', $variationKey);
            $keyIds = [];
            $configurableAttribute = [];

            foreach ($compositeKeys as $compositeKey) {
                list($attributeKey, $optionKey) = explode(':', $compositeKey);
                $attribute = $attributesData[$attributeKey];

                $keyIds[] = $attribute['options'][$optionKey]['id'];
                $configurableAttribute[] = sprintf(
                    '"%s":"%s"',
                    isset($attribute['attribute_code']) ? $attribute['attribute_code'] : $attribute['frontend_label'],
                    $attribute['options'][$optionKey]['id']
                );
            }

            $keyIds = implode('-', $keyIds);
            $variation['configurable_attribute'] = '{' . implode(',', $configurableAttribute) . '}';
            $variation['variationKey'] = $keyIds;
            $variation['newProduct'] = 1;
            $variation['status'] = 1;
            $result[$keyIds] = $variation;
        }

        return $result;
    }

    /**
     * Prepare attributes.
     *
     * @param ConfigurableAttributesData $configurableAttributesData
     * @return array
     */
    protected function prepareAttributes(ConfigurableAttributesData $configurableAttributesData)
    {
        $ids = [];

        foreach ($configurableAttributesData->getAttributes() as $attribute) {
            /** @var CatalogProductAttribute $attribute */
            $ids[] = $attribute->getAttributeId();
        }
        return $ids;
    }

    /**
     * Prepare associated product ids.
     *
     * @param ConfigurableAttributesData $configurableAttributesData
     * @return array
     */
    protected function prepareAssociatedProductIds(ConfigurableAttributesData $configurableAttributesData)
    {
        $productIds = [];

        foreach ($configurableAttributesData->getProducts() as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }
}
