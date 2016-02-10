<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Ui\Component\Form;

/**
 * Data provider for the form of adding new product attribute.
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $scopes = [
            [
                'value' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'label' => __('Store View')
            ],
            [
                'value' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'label' => __('Website')
            ],
            [
                'value' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'label' => __('Global')
            ]
        ];

        $meta['advanced_fieldset']['children'] = [
            'is_global' => ['options' => $scopes],
            'attribute_code' => [
                'notice' => __(
                    "This is used internally. Make sure you don't use spaces or more than %1 symbols.",
                    EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'validation' => [
                    'validate-length' => true,
                    'maximum-length-' . EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH => true
                ]
            ]
        ];

        $meta['base_fieldset']['children']['attribute_options'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'dataScope' => 'attribute_options',
                        'addButtonLabel' => __('Add Value')
                    ]
                ]
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
//                                'positionProvider' => 'attribute_options_container.position'
                            ],
                        ],
                    ],
                    'children' => [
                        'attribute_options_container' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'fieldset',
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                'is_default' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => 'field',
                                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                                'formElement' => Form\Element\Checkbox::NAME,
                                                'label' => __('Is Default'),
                                                'dataScope' => 'is_default',
                                                'prefer' => 'radio',
                                                'value' => '1',
                                                'sortOrder' => 10,
                                            ],
                                        ],
                                    ],
                                ],
                                'options[0]' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Form\Field::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'dataScope' => 'option[0]',
                                                'formElement' => Form\Element\Input::NAME,
                                                'label' => __('Default Store View'),
                                                'sortOrder' => 20,
                                            ]
                                        ]
                                    ]
                                ],
                                'options[1]' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Form\Field::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'dataScope' => 'options[1]',
                                                'formElement' => Form\Element\Input::NAME,
                                                'label' => __('Admin'),
                                                'sortOrder' => 30,
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $meta;
    }
}
