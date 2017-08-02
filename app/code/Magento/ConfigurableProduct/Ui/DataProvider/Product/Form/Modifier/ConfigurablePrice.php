<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

/**
 * Data provider for price in the Configurable products
 * @since 2.1.0
 */
class ConfigurablePrice extends AbstractModifier
{
    const CODE_GROUP_PRICE = 'container_price';

    /**
     * @var string
     * @since 2.1.0
     */
    private static $advancedPricingButton = 'advanced_pricing_button';

    /**
     * @var LocatorInterface
     * @since 2.1.0
     */
    private $locator;

    /**
     * @param LocatorInterface $locator
     * @since 2.1.0
     */
    public function __construct(
        LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyMeta(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, ProductAttributeInterface::CODE_PRICE)
            ?: $this->getGroupCodeByField($meta, self::CODE_GROUP_PRICE);

        if ($groupCode && !empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
            if (!empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            ProductAttributeInterface::CODE_PRICE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'component' => 'Magento_ConfigurableProduct/js/' .
                                                'components/price-configurable'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
            if (!empty(
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE]['children'][self::$advancedPricingButton]
            )) {
                $productTypeId = $this->locator->getProduct()->getTypeId();
                $visibilityConfig = ($productTypeId === ConfigurableType::TYPE_CODE)
                    ? ['visible' => 0, 'disabled' => 1]
                    : [
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = '
                                . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                        ]
                    ];
                $config = $visibilityConfig;
                $config['componentType'] = 'container';
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            self::$advancedPricingButton => [
                                'arguments' => [
                                    'data' => [
                                        'config' => $config,
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
