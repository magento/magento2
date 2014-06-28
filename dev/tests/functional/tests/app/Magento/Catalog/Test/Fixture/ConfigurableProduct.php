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
namespace Magento\Catalog\Test\Fixture;

use Mtf\System\Config;
use Mtf\Factory\Factory;
use Magento\Catalog\Test\Repository\ConfigurableProduct as Repository;

/**
 * Class ConfigurableProduct
 * Configurable product data
 *
 */
class ConfigurableProduct extends Product
{
    /**
     * Mapping data into ui tabs
     */
    const GROUP = 'variations';

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * Custom constructor to create configurable product with attribute
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['attribute_label_1'] = array($this, 'attributeProvider');
    }

    /**
     * Provide data to product from new attribute
     *
     * @param ProductAttribute $attribute
     * @return void
     */
    public function provideNewAttributeData(ProductAttribute $attribute)
    {
        $options = $attribute->getOptionLabels();
        $placeholders['new_attribute_label'] = $attribute->getAttributeLabel();
        $placeholders['new_attribute_option_1_label'] = $options[0];
        $placeholders['new_attribute_option_2_label'] = $options[1];
        $this->_applyPlaceholders($this->_data, $placeholders);
    }

    /**
     * Create new configurable attribute and add it to product
     *
     * @return string
     */
    protected function attributeProvider()
    {
        $attribute = Factory::getFixtureFactory()->getMagentoCatalogProductAttribute();
        $attribute->switchData('configurable_attribute');
        $attribute->persist();
        $this->_dataConfig['attributes']['id'][] = $attribute->getAttributeId();
        $this->_dataConfig['attributes'][$attribute->getAttributeId()]['code'] = $attribute->getAttributeCode();
        $this->_dataConfig['options'][$attribute->getAttributeId()]['id'] = $attribute->getOptionIds();

        $options = $attribute->getOptionLabels();
        $placeholders['attribute_1_name'] = $attribute->getAttributeLabel();
        $placeholders['attribute_1_option_label_1'] = $options[0];
        $placeholders['attribute_1_option_label_2'] = $options[1];
        $this->_applyPlaceholders($this->_data, $placeholders);

        return $attribute->getAttributeLabel();
    }

    /**
     * Create product
     * Get affected attribute set
     *
     * @return string|null
     */
    public function getAffectedAttributeSet()
    {
        return $this->getData('affect_configurable_product_attributes')
            ? $this->getData('affect_configurable_product_attributes')
            : null;
    }

    /**
     * Returns the sku for the specified option.
     *
     * @param string $selectedOption
     * @return string
     */
    public function getVariationSku($selectedOption)
    {
        $sku = '';
        foreach ($this->getData('fields/variations-matrix/value') as $variation) {
            $configurableAttributes = $variation['configurable_attribute'];
            foreach ($configurableAttributes as $configurableAttribute) {
                $attributeOption = $configurableAttribute['attribute_option'];
                if ($attributeOption === $selectedOption) {
                    $sku = $variation['value']['name']['value'];
                    break 2;
                }
            }
        }
        return $sku;
    }

    /**
     * Get variations SKUs
     *
     * @return $this|ConfigurableProduct
     */
    public function getVariationSkus()
    {
        $variationSkus = array();
        foreach ($this->getVariationsMatrix() as $variation) {
            if (is_array($variation)) {
                $variationSkus[] = $variation['value']['name']['value'];
            }
        }

        return $variationSkus;
    }

    /**
     * Get variations matrix
     *
     * @return array
     */
    public function getVariationsMatrix()
    {
        $variations = $this->getData('fields/variations-matrix/value');
        return is_array($variations) ? $variations : array();
    }

    /**
     * Create product
     *
     * @return $this|ConfigurableProduct
     */
    public function persist()
    {
        $id = Factory::getApp()->magentoCatalogCreateConfigurable($this);
        $this->_data['id']['value'] = $id;

        return $this;
    }

    /**
     * Get configurable attributes data
     *
     * @return array
     */
    public function getConfigurableAttributes()
    {
        $attributes = $this->getData('fields/configurable_attributes_data/value');
        return is_array($attributes) ? $attributes : array();
    }

    /**
     * Get configurable product options
     *
     * @return array
     */
    public function getConfigurableOptions()
    {
        $options = array();
        foreach ($this->getConfigurableAttributes() as $attribute) {
            foreach ($attribute as $option) {
                if (isset($option['option_label']['value'])) {
                    $options[$attribute['label']['value']][] = $option['option_label']['value'];
                }
            }
        }
        return $options;
    }

    /**
     * Init Data
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _initData()
    {
        parent::_initData();
        $this->_dataConfig = array(
            'constraint' => 'Success',

            'create_url_params' => array(
                'type' => Repository::CONFIGURABLE,
                'set' => static::DEFAULT_ATTRIBUTE_SET_ID,
            ),
        );
        $data = array(
            'fields' => array(
                'price' => array(
                    'value' => '10',
                    'group' => static::GROUP_PRODUCT_DETAILS
                ),
                'tax_class_id' => array(
                    'value' => 'Taxable Goods',
                    'group' => static::GROUP_PRODUCT_DETAILS,
                    'input' => 'select',
                    'input_value' => '2',
                ),
                'weight' => array(
                    'value' => '1',
                    'group' => static::GROUP_PRODUCT_DETAILS
                ),
                'product_website_1' => array(
                    'value' => 'Yes',
                    'input_value' => array(1),
                    'group' => static::GROUP_PRODUCT_WEBSITE,
                    'input' => 'checkbox',
                    'input_name' => 'website_ids'
                ),
                'configurable_attributes_data' => array(
                    'value' => array(
                            '0' => array(
                                'label' => array(
                                    'value' => '%attribute_label_1%'
                                ),
                                '0' => array(
                                    'option_label' => array(
                                        'value' => '%attribute_1_option_label_1%'
                                    ),
                                    'pricing_value' => array(
                                        'value' => '1'
                                    ),
                                    'is_percent' => array(
                                        'value' => 'No'
                                    ),
                                    'include' => array(
                                        'value' => 'Yes'
                                    ),
                                ),
                                '1' => array(
                                    'option_label' => array(
                                        'value' => '%attribute_1_option_label_2%'
                                    ),
                                    'pricing_value' => array(
                                        'value' => '2'
                                    ),
                                    'is_percent' => array(
                                        'value' => 'No'
                                    ),
                                    'include' => array(
                                        'value' => 'Yes'
                                    ),
                                )
                            )
                        ),
                    'group' => static::GROUP
                ),
                'variations-matrix' => array(
                    'value' => array(
                        '0' => array(
                            'configurable_attribute' => array(
                                '0' => array(
                                    'attribute_option' => '%attribute_1_option_label_1%'
                                )
                            ),
                            'value' => array(
                                'display' => array(
                                    'value' => 'Yes',
                                    'input' => 'checkbox'
                                ),
                                'name' => array(
                                    'value' => 'Variation 0-%isolation%'
                                ),
                                'sku' => array(
                                    'value' => 'Variation 0-%isolation%'
                                ),
                                'qty' => array(
                                    'value' => '100'
                                )
                            )
                        ),
                        '1' => array(
                            'configurable_attribute' => array(
                                '0' => array(
                                    'attribute_option' => '%attribute_1_option_label_2%'
                                )
                            ),
                            'value' => array(
                                'display' => array(
                                    'value' => 'Yes',
                                    'input' => 'checkbox'
                                ),
                                'name' => array(
                                    'value' => 'Variation 1-%isolation%'
                                ),
                                'sku' => array(
                                    'value' => 'Variation 1-%isolation%'
                                ),
                                'qty' => array(
                                    'value' => '200'
                                )
                            )
                        )
                    ),
                    'group' => static::GROUP
                ),
            ),
            'checkout' => array(
                'selections' => array(
                    '0' => array(
                        'attribute_name' => '%attribute_1_name%',
                        'option_name' => '%attribute_1_option_label_1%'
                    )
                ),
                'special_price' => '10'
            )
        );

        $this->_data = array_merge_recursive($this->_data, $data);

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogConfigurableProduct($this->_dataConfig, $this->_data);
    }

    /**
     * Get configurable options
     *
     * @return array
     */
    public function getProductOptions()
    {
        $selections = $this->getData('checkout/selections');
        $options = array();
        foreach ($selections as $selection) {
            $options[$selection['attribute_name']] = $selection['option_name'];
        }
        return $options;
    }

    /**
     * Return special price for first configurable option
     * This value is used to validate value on the cart and order
     *
     * @return string
     */
    public function getProductSpecialPrice()
    {
        return $this->getData('checkout/special_price');
    }

    /**
     * Get product options price
     *
     * @return float|int
     */
    public function getProductOptionsPrice()
    {
        $price = 0;
        $selections = $this->getData('checkout/selections');
        foreach ($selections as $selection) {
            $optionName = $selection['option_name'];
            $attributes = $this->getData('fields/configurable_attributes_data/value');
            foreach ($attributes as $attribute) {
                $optionCount = 0;
                while (isset($attribute[$optionCount])) {
                    if ($attribute[$optionCount]['option_label']['value'] == $optionName) {
                        $price += $attribute[$optionCount]['pricing_value']['value'];
                    }
                    ++$optionCount;
                }
            }
        }
        return $price;
    }

    /**
     * Prepare edit configurable product data
     *
     * @return $this
     */
    public function getEditData()
    {
        $data = $this->getData();
        $this->switchData('edit_configurable');
        $editData = $this->getData();
        $data['fields']['variations-matrix'] = $editData['fields']['variations-matrix'];
        $this->_data = array_replace_recursive($data, $editData);
        return $this;
    }
}
