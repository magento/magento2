<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

/**
 * Tier prices modifier adds price type option to tier prices.
 *
 * @api
 * @since 101.1.0
 */
class TierPrice extends AbstractModifier
{
    /**
     * @var ProductPriceOptionsInterface
     */
    private $productPriceOptions;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ProductPriceOptionsInterface $productPriceOptions
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ProductPriceOptionsInterface $productPriceOptions,
        ArrayManager $arrayManager
    ) {
        $this->productPriceOptions = $productPriceOptions;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     * @since 101.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Add tier price info to meta array.
     *
     * @since 101.1.0
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $tierPricePath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_TIER_PRICE,
            $meta,
            null,
            'children'
        );
        if ($tierPricePath) {
            $pricePath =  $this->arrayManager->findPath(
                ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE,
                $meta,
                $tierPricePath
            );

            if ($pricePath) {
                $priceMeta = $this->arrayManager->get($pricePath, $meta);
                $updatedStructure = $this->getUpdatedTierPriceStructure($priceMeta);
                $meta = $this->arrayManager->remove($pricePath, $meta);
                $meta = $this->arrayManager->merge(
                    $this->arrayManager->slicePath($pricePath, 0, -1),
                    $meta,
                    $updatedStructure
                );
            }
        }
        return $meta;
    }

    /**
     * Get updated tier price structure.
     *
     * @param array $priceMeta
     * @return array
     */
    private function getUpdatedTierPriceStructure(array $priceMeta)
    {
        $priceTypeOptions = $this->productPriceOptions->toOptionArray();
        $firstOption = $priceTypeOptions ? current($priceTypeOptions) : null;

        $priceMeta['arguments']['data']['config']['visible'] = $firstOption
            && $firstOption['value'] == ProductPriceOptionsInterface::VALUE_FIXED;
        $priceMeta['arguments']['data']['config']['validation'] = [
            'validate-zero-or-greater' => true
        ];
        return [
            'price_value' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'formElement' => Container::NAME,
                            'dataType' => Price::NAME,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'label' => __('Price'),
                            'enableLabel' => true,
                            'dataScope' => '',
                            'additionalClasses' => 'control-grouped',
                            'sortOrder' => isset($priceMeta['arguments']['data']['config']['sortOrder'])
                                ? $priceMeta['arguments']['data']['config']['sortOrder'] : 40,
                        ],
                    ],
                ],
                'children' => [
                    ProductAttributeInterface::CODE_TIER_PRICE_FIELD_VALUE_TYPE => [
                        'arguments' => [
                            'data' => [
                                'options' => $priceTypeOptions,
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Select::NAME,
                                    'dataType' => 'text',
                                    'component' => 'Magento_Catalog/js/tier-price/value-type-select',
                                    'prices' => [
                                        ProductPriceOptionsInterface::VALUE_FIXED => '${ $.parentName }.'
                                            . ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE,
                                        ProductPriceOptionsInterface::VALUE_PERCENT => '${ $.parentName }.'
                                            . ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PERCENTAGE_VALUE,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE => $priceMeta,
                    ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PERCENTAGE_VALUE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'dataType' => Price::NAME,
                                    'addbefore' => '%',
                                    'validation' => [
                                        'required-entry' => true,
                                        'validate-positive-percent-decimal' => true
                                    ],
                                    'visible' => $firstOption
                                        && $firstOption['value'] == ProductPriceOptionsInterface::VALUE_PERCENT,
                                ],
                            ],
                        ],
                    ],
                    'price_calc' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Container::NAME,
                                    'component' => 'Magento_Catalog/js/tier-price/percentage-processor',
                                    'visible' => false
                                ],
                            ],
                        ]
                    ]
                ],
            ],
        ];
    }
}
