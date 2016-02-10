<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\AttributeConstantsInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Ui\Component\Form;
use Magento\Catalog\Ui\DataProvider\Grouper;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for main panel of product page
 */
class General extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var Grouper
     */
    protected $grouper;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param Grouper $grouper
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        Grouper $grouper,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->grouper = $grouper;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $data = $this->customizeNumberFormat($data);
        $data = $this->customizeAdvancedPriceFormat($data);
        $modelId = $this->locator->getProduct()->getId();

        if (!isset($data[$modelId][static::DATA_SOURCE_DEFAULT][AttributeConstantsInterface::CODE_STATUS])) {
            $data[$modelId][static::DATA_SOURCE_DEFAULT][AttributeConstantsInterface::CODE_STATUS] = '1';
        }

        return $data;
    }

    /**
     * Customizing number fields
     *
     * @param array $data
     * @return array
     */
    protected function customizeNumberFormat(array $data)
    {
        $model = $this->locator->getProduct();
        $modelId = $model->getId();
        $numberFields = [
            AttributeConstantsInterface::CODE_PRICE,
            AttributeConstantsInterface::CODE_WEIGHT,
            AttributeConstantsInterface::CODE_SPECIAL_PRICE,
            AttributeConstantsInterface::CODE_COST,
        ];

        foreach ($numberFields as $fieldCode) {
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/' . $fieldCode;
            $number = (float)$this->arrayManager->get($path, $data);
            $data = $this->arrayManager->replace(
                $path,
                $data,
                $this->formatNumber($number)
            );
        }

        return $data;
    }

    /**
     * Formatting numeric field
     *
     * @param float $number
     * @param int $decimals
     * @return string
     */
    protected function formatNumber($number, $decimals = 2)
    {
        return number_format($number, $decimals);
    }

    /**
     * Customizing number fields for advanced price
     *
     * @param array $data
     * @return array
     */
    protected function customizeAdvancedPriceFormat(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        $fieldCode = AttributeConstantsInterface::CODE_TIER_PRICE;

        if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT][$fieldCode])) {
            foreach ($data[$modelId][self::DATA_SOURCE_DEFAULT][$fieldCode] as &$value) {
                $value[AttributeConstantsInterface::CODE_TIER_PRICE_FIELD_PRICE] =
                    $this->formatNumber($value[AttributeConstantsInterface::CODE_TIER_PRICE_FIELD_PRICE]);
                $value[AttributeConstantsInterface::CODE_TIER_PRICE_FIELD_PRICE_QTY] =
                    (int)$value[AttributeConstantsInterface::CODE_TIER_PRICE_FIELD_PRICE_QTY];
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->prepareFirstPanel($meta);
        $meta = $this->customizeStatusField($meta);
        $meta = $this->customizeWeightField($meta);
        $meta = $this->customizeNewDateRangeField($meta);
        $meta = $this->customizeNameListeners($meta);

        return $meta;
    }

    /**
     * Disable collapsible and set empty label
     *
     * @param array $meta
     * @return array
     */
    protected function prepareFirstPanel(array $meta)
    {
        $generalPanelName = $this->getGeneralPanelName($meta);

        $meta[$generalPanelName]['arguments']['data']['config']['label'] = '';
        $meta[$generalPanelName]['arguments']['data']['config']['collapsible'] = false;

        return $meta;
    }

    /**
     * Customize Status field
     *
     * @param array $meta
     * @return array
     */
    protected function customizeStatusField(array $meta)
    {
        $switcherConfig = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'formElement' => Form\Element\Checkbox::NAME,
                        'componentType' => Form\Field::NAME,
                        'prefer' => 'toggle',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '2'
                        ],
                    ],
                ],
            ],
        ];

        $path = $this->getElementArrayPath($meta, AttributeConstantsInterface::CODE_STATUS);
        $meta = $this->arrayManager->merge($path, $meta, $switcherConfig);

        return $meta;
    }

    /**
     * Customize Weight filed
     *
     * @param array $meta
     * @return array
     */
    protected function customizeWeightField(array $meta)
    {
        if ($weightPath = $this->getElementArrayPath($meta, AttributeConstantsInterface::CODE_WEIGHT)) {
            if ($this->locator->getProduct()->getTypeId() !== \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL) {
                $weightPath = $this->getElementArrayPath($meta, AttributeConstantsInterface::CODE_WEIGHT);
                $meta = $this->arrayManager->merge(
                    $weightPath,
                    $meta,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => AttributeConstantsInterface::CODE_WEIGHT,
                                    'validation' => [
                                        'validate-number' => true,
                                    ],
                                    'additionalClasses' => 'admin__field-small',
                                    'addafter' => $this->locator->getStore()->getConfig('general/locale/weight_unit'),
                                    'imports' => [
                                        'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                                            . '.product_has_weight:value'
                                    ]
                                ],
                            ],
                        ],
                    ]
                );

                $containerPath = $this->getElementArrayPath(
                    $meta,
                    static::CONTAINER_PREFIX . AttributeConstantsInterface::CODE_WEIGHT
                );
                $meta = $this->arrayManager->merge($containerPath, $meta, [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Ui/js/form/components/group',
                            ],
                        ],
                    ],
                ]);

                $hasWeightPath = $this->arrayManager->slicePath($weightPath, 0, -1) . '/'
                    . AttributeConstantsInterface::CODE_HAS_WEIGHT;
                $meta = $this->arrayManager->set(
                    $hasWeightPath,
                    $meta,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'boolean',
                                    'formElement' => Form\Element\Select::NAME,
                                    'componentType' => Form\Field::NAME,
                                    'dataScope' => 'product_has_weight',
                                    'label' => '',
                                    'options' => [
                                        [
                                            'label' => __('This item has weight'),
                                            'value' => 1
                                        ],
                                        [
                                            'label' => __('This item has no weight'),
                                            'value' => 0
                                        ],
                                    ],
                                    'value' => (int)$this->locator->getProduct()->getTypeInstance()->hasWeight(),
                                ],
                            ],
                        ]
                    ]
                );

                $meta = $this->grouper->groupMetaElements(
                    $meta,
                    [AttributeConstantsInterface::CODE_WEIGHT, AttributeConstantsInterface::CODE_HAS_WEIGHT],
                    [
                        'meta' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataScope' => '',
                                        'breakLine' => false,
                                        'scopeLabel' => $this->arrayManager->get($weightPath . '/scopeLabel', $meta)
                                    ],
                                ],
                            ],
                        ],
                        'targetCode' => 'container_' . AttributeConstantsInterface::CODE_WEIGHT
                    ]
                );
            }
        }

        return $meta;
    }

    /**
     * Customize "Set Product as New" date fields
     *
     * @param array $meta
     * @return array
     */
    protected function customizeNewDateRangeField(array $meta)
    {
        $mainElement = 'news_from_date';

        $mainElementPath = $this->getElementArrayPath($meta, $mainElement);
        $meta = $this->grouper->groupMetaElements(
            $meta,
            [
                $mainElement => [
                    'meta' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Set Product as New From'),
                                    'scopeLabel' => null,
                                    'additionalClasses' => 'admin__field-date'
                                ],
                            ],
                        ],
                    ]
                ],
                'news_to_date' => [
                    'meta' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('To'),
                                    'scopeLabel' => null,
                                    'additionalClasses' => 'admin__field-date',
                                ],
                            ],
                        ]
                    ]
                ]
            ],
            [
                'targetCode' => 'news_date_range',
                'meta' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set Product as New From'),
                                'additionalClasses' => 'admin__control-grouped-date',
                                'breakLine' => false,
                                'scopeLabel' => $this->arrayManager->get($mainElementPath . '/scopeLabel', $meta),
                            ],
                        ],
                    ],
                ]
            ]
        );

        return $meta;
    }

    /**
     * Add links for fields depends of product name
     *
     * @param array $meta
     * @return array
     */
    protected function customizeNameListeners(array $meta)
    {
        $listeners = [
            AttributeConstantsInterface::CODE_SKU,
            AttributeConstantsInterface::CODE_SEO_FIELD_META_TITLE,
            AttributeConstantsInterface::CODE_SEO_FIELD_META_KEYWORD,
            AttributeConstantsInterface::CODE_SEO_FIELD_META_DESCRIPTION,
        ];
        foreach ($listeners as $listener) {
            $listenerPath = $this->getElementArrayPath($meta, $listener);
            $importsConfig = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'Magento_Catalog/js/components/import-handler',
                            'imports' => [
                                'handleChanges' => '${$.provider}:data.product.name',
                            ],
                        ],
                    ],
                ],
            ];

            $meta = $this->arrayManager->merge($listenerPath, $meta, $importsConfig);
        }

        $skuPath = $this->getElementArrayPath($meta, AttributeConstantsInterface::CODE_SKU);
        $meta = $this->arrayManager->merge(
            $skuPath,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'autoImportIfEmpty' => true,
                            'allowImport' => $this->locator->getProduct()->getId() ? false : true,
                        ],
                    ],
                ],
            ]
        );

        $namePath = $this->getElementArrayPath($meta, AttributeConstantsInterface::CODE_NAME);

        return $this->arrayManager->merge(
            $namePath,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'valueUpdate' => 'keyup'
                        ],
                    ],
                ],
            ]
        );
    }
}
