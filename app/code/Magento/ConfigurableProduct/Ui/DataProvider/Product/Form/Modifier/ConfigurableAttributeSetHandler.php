<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Form;
use Magento\Framework\UrlInterface;

/**
 * Data provider for Attribute Set handler in the Configurable products
 */
class ConfigurableAttributeSetHandler extends AbstractModifier
{
    const ATTRIBUTE_SET_HANDLER_MODAL = 'configurable_attribute_set_handler_modal';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
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
        $meta = array_merge_recursive(
            $meta,
            [
                self::ATTRIBUTE_SET_HANDLER_MODAL => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Modal::NAME,
                                'dataScope' => '',
                                'options' => [
                                    'title' => __('Choose Affected Attribute Set'),
                                    'type' => 'popup',
                                ],
                            ],
                        ],
                    ],
                    'children' => [
                        'affected-attribute-set-current' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'formElement' => Form\Element\Checkbox::NAME,
                                        'prefer' => 'radio',
                                        'description' => __('Add configurable attributes to the current Attribute Set'),
                                        'dataScope' => 'configurable-affected-attribute-set',
                                        'label' => ' ',
                                        'valueMap' => [
                                            'true' => 'current',
                                            'false' => '0',
                                        ],
                                        'value' => 'current',
                                        'sortOrder' => 10,
                                    ],
                                ],
                            ],
                        ],
                        'affected-attribute-set-new' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'formElement' => Form\Element\Checkbox::NAME,
                                        'prefer' => 'radio',
                                        'description' => __(
                                            'Add configurable attributes to the new Attribute Set based on current'
                                        ),
                                        'dataScope' => 'configurable-affected-attribute-set',
                                        'label' => ' ',
                                        'valueMap' => [
                                            'true' => 'new',
                                            'false' => '0',
                                        ],
                                        'value' => '0',
                                        'sortOrder' => 20,
                                    ],
                                ],
                            ],
                        ],
                        'configurable_new_attribute_set_name' => $this->getNewAttributeSet(),
                        'affected-attribute-set-existing' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'formElement' => Form\Element\Checkbox::NAME,
                                        'prefer' => 'radio',
                                        'description' => __(
                                            'Add configurable attributes to the existing Attribute Set'
                                        ),
                                        'dataScope' => 'configurable-affected-attribute-set',
                                        'label' => ' ',
                                        'valueMap' => [
                                            'true' => 'existing',
                                            'false' => '0',
                                        ],
                                        'value' => '0',
                                        'sortOrder' => 40,
                                    ],
                                ],
                            ],
                        ],
                        'confirmButtonContainer' => $this->getConfirmButton(),
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Returns confirm button configuration
     *
     * @return array
     */
    protected function getConfirmButton()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => 100,
                    ],
                ],
            ],
            'children' => [
                'confirm_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'displayAsLink' => true,
                                'actions' => [
                                    [
                                        'targetName' => 'ns = ${ $.ns }, index='
                                            . self::ATTRIBUTE_SET_HANDLER_MODAL,
                                        'actionName' => 'closeModal',
                                    ],
                                    [
                                        'targetName' => 'product_form.product_form',
                                        'actionName' => 'save',
                                        'params' => [
                                            false
                                        ]
                                    ],
                                ],
                                'title' => __('Confirm'),
                                'sortOrder' => 10
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns new attribute set input configuration
     *
     * @return array
     */
    protected function getNewAttributeSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'componentType' => Form\Field::NAME,
                        'dataScope' => 'configurable_new_attribute_set_name',
                        'label' => __('New Attribute Set Name'),
                        'sortOrder' => 30,
                        'validation' => ['required-entry' => true],
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = affected-attribute-set-new:checked',
                            'disabled' =>
                                '!ns = ${ $.ns }, index = affected-attribute-set-new:checked',
                        ]
                    ],
                ],
            ],
        ];
    }
}
