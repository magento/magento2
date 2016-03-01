<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\AttributeConstantsInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Customize Weight field
 */
class BundleWeight extends AbstractModifier
{
    const CODE_WEIGHT_TYPE = 'weight_type';
    const CODE_CONTAINER_WEIGHT = 'container_weight';
    const SORT_ORDER = 61;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (($groupCode = $this->getGroupCodeByField($meta, AttributeConstantsInterface::CODE_WEIGHT)
            ?: $this->getGroupCodeByField($meta, self::CODE_CONTAINER_WEIGHT))
        ) {
            $weightPath = $this->arrayManager->findPath(
                AttributeConstantsInterface::CODE_WEIGHT,
                $meta,
                null,
                'children'
            ) ?: $this->arrayManager->findPath(static::CODE_CONTAINER_WEIGHT, $meta, null, 'children');
            $meta[$groupCode]['children'][self::CODE_WEIGHT_TYPE] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'sortOrder' => $this->getNextAttributeSortOrder(
                                $meta,
                                [self::CODE_CONTAINER_WEIGHT],
                                self::SORT_ORDER
                            ),
                            'formElement' => Form\Element\Checkbox::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'label' => __('Dynamic Weight'),
                            'prefer' => 'toggle',
                            'additionalClasses' => 'admin__field-x-small',
                            'templates' => [
                                'checkbox' => 'ui/form/components/single/switcher',
                            ],
                            'valueMap' => [
                                'false' => '1',
                                'true' => '0',
                            ],
                            'dataScope' => self::CODE_WEIGHT_TYPE,
                            'value' => '0',
                            'scopeLabel' => $this->arrayManager->get($weightPath . '/scopeLabel', $meta),
                        ],
                    ],
                ],
            ];

            $meta[$groupCode]['children'][self::CODE_CONTAINER_WEIGHT] = array_replace_recursive(
                $meta[$groupCode]['children'][self::CODE_CONTAINER_WEIGHT],
                [
                    'children' => [
                        AttributeConstantsInterface::CODE_HAS_WEIGHT => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'disabled' => true,
                                        'visible' => false,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            );
            $meta[$groupCode]['children'][self::CODE_CONTAINER_WEIGHT] = array_replace_recursive(
                $meta[$groupCode]['children'][self::CODE_CONTAINER_WEIGHT],
                [
                    'children' => [
                        AttributeConstantsInterface::CODE_WEIGHT => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'imports' => [
                                            'disabled' => 'ns = ${ $.ns }, index = '
                                                . self::CODE_WEIGHT_TYPE . ':checked',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
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
