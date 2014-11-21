<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

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
