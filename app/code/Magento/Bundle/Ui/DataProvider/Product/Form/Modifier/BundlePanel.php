<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Modal;
use Magento\Framework\UrlInterface;
use Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type as Shipment;

/**
 * Create Ship Bundle Items and Affect Bundle Product Selections fields
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundlePanel extends AbstractModifier
{
    const GROUP_CONTENT = 'content';
    const CODE_SHIPMENT_TYPE = 'shipment_type';
    const SORT_ORDER = 20;
    const CODE_BUNDLE_DATA = 'bundle_data';
    const CODE_AFFECT_BUNDLE_PRODUCT_SELECTIONS = 'affect_bundle_product_selections';
    const CODE_BUNDLE_HEADER = 'bundle_header';
    const CODE_BUNDLE_OPTIONS = 'bundle_options';
    const CODE_MODAL_OPTIONS = 'modal';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Shipment
     */
    protected $shipment;

    /**
     * @param UrlInterface $urlBuilder
     * @param Shipment $shipment
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Shipment $shipment
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->shipment = $shipment;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $generalPanel = $this->getGeneralPanelName($meta);

        $meta = array_replace_recursive(
            $meta,
            [
                self::CODE_BUNDLE_DATA => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Bundle Items'),
                                    'componentType' => isset($meta[$generalPanel]['componentType'])
                                        ? $meta[$generalPanel]['componentType']
                                        : Form\Fieldset::NAME,
                                    'dataScope' => '',
                                    'sortOrder' =>
                                        $this->getNextGroupSortOrder($meta, self::GROUP_CONTENT, self::SORT_ORDER),
                                    'collapsible' => true,
                                    'opened' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        'modal' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'isTemplate' => false,
                                        'componentType' => Modal::NAME,
                                        'dataScope' => '',
                                        'provider' => 'product_form.product_form_data_source',
                                        'options' => [
                                            'title' => __('Add Products to Option'),
                                            'buttons' => [
                                                [
                                                    'text' => __('Cancel'),
                                                    'class' => 'action-secondary',
                                                    'actions' => ['closeModal'],
                                                ],
                                                [
                                                    'text' => __('Add Selected Products'),
                                                    'class' => 'action-primary',
                                                    'actions' => [
                                                        [
                                                            'targetName' => 'index = bundle_product_listing',
                                                            'actionName' => 'save'
                                                        ],
                                                        'closeModal'
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'children' => [
                                'bundle_product_listing' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'autoRender' => false,
                                                'componentType' => 'insertListing',
                                                'dataScope' => 'bundle_product_listing',
                                                'externalProvider' =>
                                                    'bundle_product_listing.bundle_product_listing_data_source',
                                                'selectionsProvider' =>
                                                    'bundle_product_listing.bundle_product_listing.product_columns.ids',
                                                'ns' => 'bundle_product_listing',
                                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                                'realTimeLink' => false,
                                                'dataLinks' => ['imports' => false, 'exports' => true],
                                                'behaviourType' => 'simple',
                                                'externalFilterMode' => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        self::CODE_SHIPMENT_TYPE => $this->getShipmentType(),
                        self::CODE_AFFECT_BUNDLE_PRODUCT_SELECTIONS => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Form\Field::NAME,
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'formElement' => Form\Element\Input::NAME,
                                        'dataScope' => 'data.affect_bundle_product_selections',
                                        'visible' => false,
                                        'value' => '1'
                                    ],
                                ],
                            ],
                        ],
                        self::CODE_BUNDLE_HEADER => $this->getBundleHeader(),
                        self::CODE_BUNDLE_OPTIONS => $this->getBundleOptions()
                    ]
                ]
            ]
        );
        if (!empty($meta[$generalPanel]['children'][self::CODE_SHIPMENT_TYPE])) {
            unset($meta[$generalPanel]['children'][self::CODE_SHIPMENT_TYPE]);
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

    /**
     * Get Shipment Type configuration
     *
     * @return array
     */
    protected function getShipmentType()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'validation' => ['required-entry' => false],
                        'dataScope' => 'data.product.shipment_type',
                        'componentType' => 'select',
                        'label' => __('Ship Bundle Items'),
                        'options' => $this->shipment->getAllOptions(),
                        'scopeLabel' => __('[GLOBAL]'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get bundle header structure
     *
     * @return array
     */
    protected function getBundleHeader()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => 10,
                    ],
                ],
            ],
            'children' => [
                'add_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Option'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'product_form.product_form.'
                                            . self::CODE_BUNDLE_DATA . '.' . self::CODE_BUNDLE_OPTIONS,
                                        'actionName' => 'addChild',
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get Bundle Options structure
     *
     * @return array
     */
    protected function getBundleOptions()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'label' => '',
                        'additionalClasses' => 'admin__field-wide',
                        'itemTemplate' => 'record',
                        'collapsibleHeader' => true,
                        'columnsHeader' => false,
                        'deleteProperty' => false,
                        'addButton' => false,
                        'dataScope' => 'data.bundle_options',
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'headerLabel' => __('New Option'),
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => 'product_bundle_container.position',
                                'imports' => [
                                    'label' => '${ $.name }' . '.product_bundle_container.option_info.title:value'
                                ],
                            ],
                        ],
                    ],
                    'children' => [
                        'product_bundle_container' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'fieldset',
                                        'label' => '',
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                'option_info' => $this->getOptionInfo(),
                                'position' => $this->getHiddenColumn('position', 20),
                                'option_id' => $this->getHiddenColumn('option_id', 30),
                                'delete' => $this->getHiddenColumn('delete', 40),
                                'bundle_selections' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => DynamicRows::NAME,
                                                'label' => '',
                                                'sortOrder' => 50,
                                                'additionalClasses' => 'admin__field-wide',
                                                'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                                                'template' => 'ui/dynamic-rows/templates/default',
                                                'renderDefaultRecord' => true,
                                                'columnsHeader' => false,
                                                'columnsHeaderAfterRender' => true,
                                                'recordTemplate' => 'record',
                                                'provider' => 'product_form.product_form_data_source',
                                                'dataProvider' => '${ $.dataScope }' . '.bundle_button_proxy',
                                                'map' => [
                                                    'id' => 'entity_id',
                                                    'product_id' => 'entity_id',
                                                    'name' => 'name',
                                                    'sku' => 'sku',
                                                    'price' => 'price',
                                                ],
                                                'links' => [
                                                    'insertData' => '${ $.provider }:${ $.dataProvider }'
                                                ],
                                                'source' => 'product',
                                                'addButton' => false,
                                            ],
                                        ],
                                    ],
                                    'children' => [
                                        'record' => $this->getBundleSelections(),
                                    ]
                                ],
                                'modal_set' => $this->getModalSet(),
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Prepares configuration for the hidden columns
     *
     * @param string $columnName
     * @param int $sortOrder
     * @return array
     */
    protected function getHiddenColumn($columnName, $sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'dataScope' => $columnName,
                        'visible' => false,
                        'additionalClasses' => ['_hidden' => true],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }


    /**
     * Get configuration for the modal set: modal and trigger button
     *
     * @return array
     */
    protected function getModalSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'sortOrder' => 60,
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'dataScope' => 'bundle_button_proxy',
                        'component' => 'Magento_Catalog/js/bundle-proxy-button',
                        'provider' => 'product_form.product_form_data_source',
                        'listingDataProvider' => 'bundle_product_listing',
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form.bundle_data.modal',
                                'actionName' => 'toggleModal'
                            ],
                            [
                                'targetName' => 'product_form.product_form.bundle_data.modal.bundle_product_listing',
                                'actionName' => 'render'
                            ]
                        ],
                        'title' => __('Add Products to Option'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get option info
     *
     * @return array
     */
    protected function getOptionInfo()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'breakLine' => false,
                        'sortOrder' => 10,
                    ],
                ],
            ],
            'children' => [
                'title' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataScope' => 'title',
                                'label' => __('Option Title'),
                                'sortOrder' => 10,
                                'validation' => ['required-entry' => true],
                            ],
                        ],
                    ],
                ],
                'type' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataScope' => 'type',
                                'label' => __('Input Type'),
                                'options' => [
                                    [
                                        'label' => __('Drop-down'),
                                        'value' => 'select'
                                    ],
                                    [
                                        'label' => __('Radio Buttons'),
                                        'value' => 'radio'
                                    ],
                                    [
                                        'label' => __('Checkbox'),
                                        'value' => 'checkbox'
                                    ],
                                    [
                                        'label' => __('Multiple Select'),
                                        'value' => 'multi'
                                    ]
                                ],
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
                'required' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'componentType' => Form\Field::NAME,
                                'description' => __('Required'),
                                'dataScope' => 'required',
                                'label' => ' ',
                                'value' => '1',
                                'valueMap' => [
                                    'true' => '1',
                                    'false' => '0',
                                ],
                                'sortOrder' => 30,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get bundle selections structure
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getBundleSelections()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'isTemplate' => true,
                        'component' => 'Magento_Ui/js/dynamic-rows/record',
                        'is_collection' => true,
                    ],
                ],
            ],
            'children' => [
                'selection_id' => $this->getHiddenColumn('selection_id', 10),
                'option_id' => $this->getHiddenColumn('option_id', 20),
                'product_id' => $this->getHiddenColumn('product_id', 30),
                'delete' => $this->getHiddenColumn('delete', 40),
                'is_default' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Bundle/js/components/bundle-checkbox',
                                'componentType' => Form\Field::NAME,
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'label' => __('Default'),
                                'dataScope' => 'is_default',
                                'prefer' => 'radio',
                                'value' => '1',
                                'sortOrder' => 50,
                            ],
                        ],
                    ],
                ],
                'name' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                'label' => __('Name'),
                                'dataScope' => 'name',
                                'sortOrder' => 60,
                            ],
                        ],
                    ],
                ],
                'sku' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                'label' => __('SKU'),
                                'dataScope' => 'sku',
                                'sortOrder' => 70,
                            ],
                        ],
                    ],
                ],
                'selection_price_value' => $this->getSelectionPriceValue(),
                'selection_price_type' => $this->getSelectionPriceType(),
                'selection_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Bundle/js/components/bundle-option-qty',
                                'formElement' => Form\Element\Input::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'label' => __('Default Quantity'),
                                'dataScope' => 'selection_qty',
                                'value' => '1',
                                'sortOrder' => 100,
                                'validation' => [
                                    'required-entry' => true,
                                    'validate-number' => true,
                                ],
                                'imports' => [
                                    'isInteger' => '${ $.provider }:${ $.parentScope }.selection_qty_is_integer'
                                ],
                            ],
                        ],
                    ],
                ],
                'selection_can_change_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Price::NAME,
                                'label' => __('User Defined'),
                                'dataScope' => 'selection_can_change_qty',
                                'value' => '1',
                                'valueMap' => ['true' => '1', 'false' => '0'],
                                'sortOrder' => 110,
                            ],
                        ],
                    ],
                ],
                'position' => $this->getHiddenColumn('position', 120),
                'action_delete' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'actionDelete',
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'label' => '',
                                'fit' => true,
                                'sortOrder' => 130,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get selection price value structure
     *
     * @return array
     */
    protected function getSelectionPriceValue()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Price::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Price'),
                        'dataScope' => 'selection_price_value',
                        'value' => '0.00',
                        'imports' => [
                            'visible' => '!ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked'
                        ],
                        'sortOrder' => 80,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get selection price type structure
     *
     * @return array
     */
    protected function getSelectionPriceType()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Boolean::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'label' => __('Price Type'),
                        'dataScope' => 'selection_price_type',
                        'value' => '0',
                        'options' => [
                            [
                                'label' => __('Fixed'),
                                'value' => '0'
                            ],
                            [
                                'label' => __('Percent'),
                                'value' => '1'
                            ]
                        ],
                        'imports' => [
                            'visible' => '!ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked'
                        ],
                        'sortOrder' => 90,
                    ],
                ],
            ],
        ];
    }
}
