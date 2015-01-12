<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductForm;

/**
 * Class AssertConfigurableProductForm
 * Assert form data equals fixture data
 */
class AssertConfigurableProductForm extends AssertProductForm
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * List skipped fixture fields in verify
     *
     * @var array
     */
    protected $skippedFixtureFields = [
        'id',
        'affected_attribute_set',
        'checkout_data',
    ];

    /**
     * List skipped attribute fields in verify
     *
     * @var array
     */
    protected $skippedAttributeFields = [
        'frontend_input',
        'attribute_code',
        'attribute_id',
        'is_required',
    ];

    /**
     * List skipped option fields in verify
     *
     * @var array
     */
    protected $skippedOptionFields = [
        'admin',
        'id',
        'is_default',
    ];

    /**
     * Skipped variation matrix field.
     *
     * @var array
     */
    protected $skippedVariationMatrixFields = [
        'configurable_attribute',
    ];

    /**
     * Prepares fixture data for comparison
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFixtureData(array $data, array $sortFields = [])
    {
        // filter values and reset keys in attributes data
        $attributeData = $data['configurable_attributes_data']['attributes_data'];
        foreach ($attributeData as $attributeKey => $attribute) {
            foreach ($attribute['options'] as $optionKey => $option) {
                if (isset($option['admin'])) {
                    $option['label'] = $option['admin'];
                }
                $attribute['options'][$optionKey] = array_diff_key($option, array_flip($this->skippedOptionFields));
            }
            $attribute['options'] = $this->sortDataByPath($attribute['options'], '::label');
            $attributeData[$attributeKey] = array_diff_key($attribute, array_flip($this->skippedAttributeFields));
        }
        $data['configurable_attributes_data']['attributes_data'] = $this->sortDataByPath($attributeData, '::label');

        // prepare and filter values, reset keys in variation matrix
        $variationsMatrix = $data['configurable_attributes_data']['matrix'];
        foreach ($variationsMatrix as $key => $variationMatrix) {
            $variationMatrix['display'] = isset($variationMatrix['display']) ? $variationMatrix['display'] : 'Yes';
            $variationsMatrix[$key] = array_diff_key($variationMatrix, array_flip($this->skippedVariationMatrixFields));
        }
        $data['configurable_attributes_data']['matrix'] = array_values($variationsMatrix);

        return parent::prepareFixtureData($data, $sortFields);
    }

    /**
     * Prepares form data for comparison
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFormData(array $data, array $sortFields = [])
    {
        // prepare attributes data
        $attributeData = $data['configurable_attributes_data']['attributes_data'];
        foreach ($attributeData as $attributeKey => $attribute) {
            $attribute['options'] = $this->sortDataByPath($attribute['options'], '::label');
            $attributeData[$attributeKey] = $attribute;
        }
        $data['configurable_attributes_data']['attributes_data'] = $this->sortDataByPath($attributeData, '::label');

        // filter values and reset keys in variation matrix
        $variationsMatrix = $data['configurable_attributes_data']['matrix'];
        foreach ($variationsMatrix as $key => $variationMatrix) {
            $variationsMatrix[$key] = array_diff_key($variationMatrix, array_flip($this->skippedVariationMatrixFields));
        }
        $data['configurable_attributes_data']['matrix'] = array_values($variationsMatrix);

        foreach ($sortFields as $path) {
            $data = $this->sortDataByPath($data, $path);
        }
        return $data;
    }
}
