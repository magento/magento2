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
 */
class ConfigurablePrice extends AbstractModifier
{
    const CODE_GROUP_PRICE = 'container_price';

    /**
     * @var string
     */
    private static $advancedPricingButton = 'advanced_pricing_button';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(
        LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }

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
        if ($groupCode = $this->getGroupCodeByField($meta, ProductAttributeInterface::CODE_PRICE)
            ?: $this->getGroupCodeByField($meta, self::CODE_GROUP_PRICE)
        ) {
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
            if (!empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
                $productTypeId = $this->locator->getProduct()->getTypeId();
                $visibilityConfig = ($productTypeId === ConfigurableType::TYPE_CODE)
                    ? ['visible' => 0, 'disabled' => 1]
                    : [
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = '
                                . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                        ]
                    ];
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            self::$advancedPricingButton => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => 'container',
                                            $visibilityConfig
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
