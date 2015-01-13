<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Source for attribute field.
 */
class CustomAttribute implements FixtureInterface
{
    /**
     * Attribute name.
     *
     * @var string
     */
    protected $data;

    /**
     * Attribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * Data set configuration settings.
     *
     * @var array
     */
    protected $params;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param mixed $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data)
    {
        $this->params = $params;
        if (is_array($data) && isset($data['dataSet'])) {
            /** @var CatalogProductAttribute $data */
            $data = $fixtureFactory->createByCode('catalogProductAttribute', ['dataSet' => $data['dataSet']]);
        }
        $this->data['value'] = $this->getDefaultAttributeValue($data);
        $this->data['code'] = $data->hasData('attribute_code') == false
            ? $this->createAttributeCode($data)
            : $data->getAttributeCode();
        $this->attribute = $data;
    }

    /**
     * Get default value of custom attribute considering to it's type.
     *
     * @param CatalogProductAttribute $attribute
     * @return string|null
     */
    protected function getDefaultAttributeValue(CatalogProductAttribute $attribute)
    {
        $data = $attribute->getData();
        if (isset($data['options'])) {
            foreach ($data['options'] as $option) {
                if ($option['is_default'] == 'Yes') {
                    return $option['admin'];
                }
            }
        } else {
            $defaultValue = preg_grep('/^default_value/', array_keys($data));
            return !empty($defaultValue) ? $data[array_shift($defaultValue)] : null;
        }
    }

    /**
     * Persist attribute options.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string|null $key
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
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
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Get default attribute code according to attribute label.
     *
     * @param CatalogProductAttribute $attribute
     * @return string
     */
    protected function createAttributeCode(CatalogProductAttribute $attribute)
    {
        $label = $attribute->getFrontendLabel();
        return strtolower(preg_replace('@[\W\s]+@', '_', $label));
    }
}
