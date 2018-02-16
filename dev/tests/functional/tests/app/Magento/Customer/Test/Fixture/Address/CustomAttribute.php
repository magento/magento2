<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture\Address;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\CustomerCustomAttributes\Test\Fixture\CustomerAddressAttribute;

/**
 * Source for attribute field.
 */
class CustomAttribute extends DataSource
{
    /**
     * Attribute fixture.
     *
     * @var CustomerAddressAttribute
     */
    protected $attribute;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param mixed $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data)
    {
        $this->params = $params;
        if (is_array($data) && isset($data['dataset'])) {
            /** @var CustomerAddressAttribute $data */
            $data = $fixtureFactory->createByCode('customerAddressAttribute', ['dataset' => $data['dataset']]);
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
     * In case if default value isn't set for dropdown and multiselect return first option,
     * for other types null.
     *
     * @param CustomerAddressAttribute $attribute
     * @return string|null
     */
    protected function getDefaultAttributeValue(CustomerAddressAttribute $attribute)
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
     * Return CustomerAddressAttribute fixture.
     *
     * @return CustomerAddressAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get default attribute code according to attribute label.
     *
     * @param CustomerAddressAttribute $attribute
     * @return string
     */
    protected function createAttributeCode(CustomerAddressAttribute $attribute)
    {
        $label = $attribute->getFrontendLabel();
        return strtolower(preg_replace('@[\W\s]+@', '_', $label));
    }
}
