<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Source for attribute field.
 */
class CustomAttribute extends DataSource
{
    /**
     * Attribute fixture.
     *
     * @var CatalogProductAttribute
     */
    private $attribute;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param mixed $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data)
    {
        $this->params = $params;
        if (is_array($data) && isset($data['dataset'])) {
            /** @var CatalogProductAttribute $data */
            $data = $fixtureFactory->createByCode('catalogProductAttribute', ['dataset' => $data['dataset']]);
        }
        if (is_array($data) && isset($data['value'])) {
            $this->data['value'] = $data['value'];
            $data = $data['attribute'];
        } else {
            $this->data['value'] = $this->getDefaultAttributeValue($data);
        }
        $this->data['code'] = $data->hasData('attribute_code') == false
            ? $this->createAttributeCode($data)
            : $data->getAttributeCode();
        $this->attribute = $data;
    }

    /**
     * Get default value of custom attribute considering to it's type.
     * In case if default value isn't set for dropdown and multiselect return first option, for other types null.
     *
     * @param CatalogProductAttribute $attribute
     * @return string|null
     */
    private function getDefaultAttributeValue(CatalogProductAttribute $attribute)
    {
        $data = $attribute->getData();
        $value = '';
        if (isset($data['options'])) {
            foreach ($data['options'] as $option) {
                if (isset($option['is_default']) && $option['is_default'] == 'Yes') {
                    $value = $option['admin'];
                }
            }
            if ($value == '') {
                $value = $data['options'][0]['admin'];
            }
        } else {
            $defaultValue = preg_grep('/^default_value/', array_keys($data));
            $value = !empty($defaultValue) ? $data[array_shift($defaultValue)] : null;
        }

        return $value;
    }

    /**
     * Return CatalogProductAttribute fixture.
     *
     * @return CatalogProductAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get default attribute code according to attribute label.
     *
     * @param CatalogProductAttribute $attribute
     * @return string
     */
    private function createAttributeCode(CatalogProductAttribute $attribute)
    {
        $label = $attribute->getFrontendLabel();
        return strtolower(preg_replace('@[\W\s]+@', '_', $label));
    }
}
