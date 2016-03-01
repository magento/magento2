<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\AttributeConstantsInterface;

/**
 * Data provider for price in the Configurable products
 */
class ConfigurablePrice extends AbstractModifier
{
    const CODE_GROUP_PRICE = 'container_price';

    /**
     * @var string
     */
    private static $advancedPricingButton = 'advanced_pricing_button';

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($groupCode = $this->getGroupCodeByField($meta, AttributeConstantsInterface::CODE_PRICE)
            ?: $this->getGroupCodeByField($meta, self::CODE_GROUP_PRICE)
        ) {
            if (!empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            AttributeConstantsInterface::CODE_PRICE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'imports' => [
                                                'disabled' => '!ns = ${ $.ns }, index = '
                                                    . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
            if (!empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            self::$advancedPricingButton => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'imports' => [
                                                'disabled' => '!ns = ${ $.ns }, index = '
                                                    . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                                                'visible' => '!ns = ${ $.ns }, index = '
                                                    . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
        }

        return $meta;
    }
}