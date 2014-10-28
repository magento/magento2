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
namespace Magento\ConfigurableProduct\Test\Repository;

use Magento\Catalog\Test\Fixture;
use Magento\Catalog\Test\Repository\Product;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct as ConfigurableProductFixture;

/**
 * Class Configurable Product Repository
 *
 */
class ConfigurableProduct extends Product
{
    const CONFIGURABLE = 'configurable';

    const CONFIGURABLE_ADVANCED_PRICING = 'configurable_advanced_pricing';

    const CONFIGURABLE_MAP = 'configurable_applied_map';

    const PRODUCT_VARIATIONS = 'product_variations';

    /**
     * Construct
     *
     * @param array $defaultConfig
     * @param array $defaultData
     */
    public function __construct(array $defaultConfig = array(), array $defaultData = array())
    {
        parent::__construct($defaultConfig, $defaultData);
        $this->_data[self::CONFIGURABLE]['data']['affect_configurable_product_attributes'] = 'Template %isolation%';
        $this->_data[self::CONFIGURABLE_ADVANCED_PRICING] = $this->getConfigurableAdvancedPricing();
        $this->_data[self::CONFIGURABLE_MAP] = $this->addMapToConfigurable($this->_data[self::CONFIGURABLE]);
        $this->_data[self::PRODUCT_VARIATIONS] = array(
            'config' => $defaultConfig,
            'data' => $this->buildProductVariations($defaultData)
        );
        $this->_data['edit_configurable'] = $this->editConfigurable();
    }

    /**
     * Get configurable product with advanced pricing
     *
     * @return array
     */
    protected function getConfigurableAdvancedPricing()
    {
        $pricing = array(
            'data' => array(
                'fields' => array(
                    'special_price' => array(
                        'value' => '9', 'group' => ConfigurableProductFixture::GROUP_PRODUCT_PRICING
                    )
                )
            )
        );
        $product = array_replace_recursive($this->_data[self::CONFIGURABLE], $pricing);

        return $product;
    }

    /**
     * Add advanced pricing (MAP) to configurable product
     *
     * @param array $data
     * @return array
     */
    protected function addMapToConfigurable(array $data)
    {
        $pricing = array(
            'data' => array(
                'fields' => array(
                    'msrp_display_actual_price_type' => array(
                        'value' => 'On Gesture',
                        'input_value' => '1',
                        'group' => ConfigurableProductFixture::GROUP_PRODUCT_PRICING,
                        'input' => 'select'
                    ),
                    'msrp' => array('value' => '15', 'group' => ConfigurableProductFixture::GROUP_PRODUCT_PRICING)
                )
            )
        );
        $product = array_replace_recursive($data, $pricing);

        return $product;
    }

    /**
     * Build product variations data set
     *
     * @param array $defaultData
     * @return array
     */
    protected function buildProductVariations(array $defaultData)
    {
        $data = $defaultData;
        $data['affected_attribute_set'] = 'Template %isolation%';
        $data['fields'] = array(
            'configurable_attributes_data' => array(
                'value' => array(
                    '0' => array(
                        'label' => array('value' => '%new_attribute_label%'),
                        '0' => array(
                            'option_label' => array('value' => '%new_attribute_option_1_label%'),
                            'include' => array('value' => 'Yes')
                        ),
                        '1' => array(
                            'option_label' => array('value' => '%new_attribute_option_2_label%'),
                            'include' => array('value' => 'Yes')
                        )
                    )
                ),
                'group' => ConfigurableProductFixture::GROUP
            ),
            'variations-matrix' => array(
                'value' => array(
                    '0' => array(
                        'configurable_attribute' => array(
                            '0' => array('attribute_option' => '%new_attribute_option_1_label%')
                        ),
                        'value' => array('qty' => array('value' => 100))
                    ),
                    '1' => array(
                        'configurable_attribute' => array(
                            '0' => array('attribute_option' => '%new_attribute_option_2_label%')
                        ),
                        'value' => array('qty' => array('value' => 100))
                    )
                ),
                'group' => ConfigurableProductFixture::GROUP
            )
        );
        return $data;
    }


    /**
     * Build product edit data set
     *
     * @return array
     */
    protected function editConfigurable()
    {
        $editData = array(
            'config' => [
                'type_id' => 'configurable'
            ],
            'data' => array(
                'fields' => array(
                    'name' => array(
                        'value' =>
                            substr(get_class($this), strrpos(get_class($this), '\\') + 1) . ' %isolation%_edit',
                    ),
                    'sku' => array(
                        'value' =>
                            substr(get_class($this), strrpos(get_class($this), '\\') + 1) . '_sku_%isolation%_edit',
                    ),
                    'price' => array(
                        'value' => '15',
                    ),
                    'configurable_attributes_data' => array(
                        'value' => array(
                            '0' => array(
                                '2' => array(
                                    'option_label' => array(
                                        'value' => 'Option3_%isolation%'
                                    ),
                                    'pricing_value' => array(
                                        'value' => '4'
                                    ),
                                    'is_percent' => array(
                                        'value' => 'No'
                                    ),
                                    'include' => array(
                                        'value' => 'Yes'
                                    )
                                ),
                            )
                        ),
                        'group' => ConfigurableProductFixture::GROUP
                    ),
                    'variations-matrix' => array(
                        'value' => array(
                            '0' => [
                                'value' => [
                                    'display' => array(
                                        'value' => 'Yes',
                                        'input' => 'checkbox'
                                    ),
                                ]
                            ],
                            '1' => [
                                'value' => [
                                    'display' => array(
                                        'value' => 'Yes',
                                        'input' => 'checkbox'
                                    ),
                                ]
                            ],
                            '2' => array(
                                'configurable_attribute' => array(
                                    '0' => array(
                                        'attribute_option' => 'Option3_%isolation%'
                                    )
                                ),
                                'value' => array(
                                    'display' => array(
                                        'value' => 'Yes',
                                        'input' => 'checkbox'
                                    ),
                                    'name' => array(
                                        'value' => 'Variation 2-%isolation%'
                                    ),
                                    'sku' => array(
                                        'value' => 'Variation 2-%isolation%'
                                    ),
                                    'qty' => array(
                                        'value' => '100'
                                    )
                                )
                            ),
                        ),
                        'group' => ConfigurableProductFixture::GROUP,
                    )
                )
            )
        );

        return $editData;
    }
}
