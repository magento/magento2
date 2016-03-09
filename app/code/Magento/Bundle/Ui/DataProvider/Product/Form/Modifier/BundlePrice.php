<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Model\Locator\LocatorInterface;

/**
 * Customize Price field
 */
class BundlePrice extends AbstractModifier
{
    const CODE_GROUP_PRICE = 'container_price';
    const CODE_PRICE_TYPE = 'price_type';
    const CODE_TAX_CLASS_ID = 'tax_class_id';
    const SORT_ORDER = 31;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function modifyMeta(array $meta)
    {
        if ($groupCode = $this->getGroupCodeByField($meta, ProductAttributeInterface::CODE_PRICE)
            ?: $this->getGroupCodeByField($meta, self::CODE_GROUP_PRICE)
        ) {
            $isNewProduct = ($this->locator->getProduct()->getId()) ? false : true;
            $pricePath = $this->getElementArrayPath($meta, ProductAttributeInterface::CODE_PRICE)
                ?: $this->getElementArrayPath($meta, self::CODE_GROUP_PRICE);

            $meta[$groupCode]['children'][self::CODE_PRICE_TYPE] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'sortOrder' => self::SORT_ORDER,
                            'formElement' => Form\Element\Checkbox::NAME,
                            'componentType' => Form\Field::NAME,
                            'label' => __('Dynamic Price'),
                            'prefer' => 'toggle',
                            'additionalClasses' => 'admin__field-x-small',
                            'templates' => ['checkbox' => 'ui/form/components/single/switcher'],
                            'valueMap' => [
                                'false' => '1',
                                'true' => '0',
                            ],
                            'dataScope' => self::CODE_PRICE_TYPE,
                            'value' => '0',
                            'disabled' => $isNewProduct ? false : true,
                            'scopeLabel' => $this->arrayManager->get($pricePath . '/scopeLabel', $meta),
                        ],
                    ],
                ],
            ];

            if (!empty($meta[$groupCode]['children']['container_' . self::CODE_PRICE_TYPE])) {
                $container = &$meta[$groupCode]['children']['container_' . self::CODE_PRICE_TYPE];
                $container['arguments']['data']['config']['sortOrder'] = self::SORT_ORDER;
                $container['arguments']['data']['config']['label'] = __('Dynamic Price');
            }

            if (!empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            ProductAttributeInterface::CODE_PRICE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'imports' => [
                                                'disabled' => 'ns = ${ $.ns }, index = '
                                                    . self::CODE_PRICE_TYPE . ':checked',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
            if (!empty($meta[$groupCode]['children']['container_' . self::CODE_TAX_CLASS_ID])) {
                $meta[$groupCode]['children']['container_' . self::CODE_TAX_CLASS_ID] = array_replace_recursive(
                    $meta[$groupCode]['children']['container_' . self::CODE_TAX_CLASS_ID],
                    [
                        'children' => [
                            self::CODE_TAX_CLASS_ID => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'imports' => [
                                                'disabled' => 'ns = ${ $.ns }, index = '
                                                    . self::CODE_PRICE_TYPE . ':checked'
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

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
