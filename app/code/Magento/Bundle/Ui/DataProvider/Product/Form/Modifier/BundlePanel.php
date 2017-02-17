<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;
use Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type as ShipmentType;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Modal;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * Create Ship Bundle Items and Affect Bundle Product Selections fields
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundlePanel extends AbstractModifier
{
    const GROUP_CONTENT = 'content';
    const CODE_SHIPMENT_TYPE = 'shipment_type';
    const CODE_BUNDLE_DATA = 'bundle-items';
    const CODE_AFFECT_BUNDLE_PRODUCT_SELECTIONS = 'affect_bundle_product_selections';
    const CODE_BUNDLE_HEADER = 'bundle_header';
    const CODE_BUNDLE_OPTIONS = 'bundle_options';
    const SORT_ORDER = 20;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ShipmentType
     */
    protected $shipmentType;

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
     * @param UrlInterface $urlBuilder
     * @param ShipmentType $shipmentType
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ShipmentType $shipmentType,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->shipmentType = $shipmentType;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->removeFixedTierPrice($meta);
        $path = $this->arrayManager->findPath(static::CODE_BUNDLE_DATA, $meta, null, 'children');

        $meta = $this->arrayManager->merge(
            $path,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataScope' => '',
                            'opened' => true,
                            'sortOrder' => $this->getNextGroupSortOrder(
                                $meta,
                                static::GROUP_CONTENT,
                                static::SORT_ORDER
                            )
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
        );

        //TODO: Remove this workaround after MAGETWO-49902 is fixed
        $bundleItemsGroup = $this->arrayManager->get($path, $meta);
        $meta = $this->arrayManager->remove($path, $meta);
        $meta = $this->arrayManager->set($path, $meta, $bundleItemsGroup);

        $meta = $this->modifyShipmentType($meta);

        return $meta;
    }

    /**
     * Remove option with fixed tier price from config.
     *
     * @param array $meta
     * @return array
     */
    private function removeFixedTierPrice(array $meta)
    {
        $tierPricePath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_TIER_PRICE,
            $meta,
            null,
            'children'
        );
        $pricePath =  $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE,
            $meta,
            $tierPricePath
        );
        $pricePath = $this->arrayManager->slicePath($pricePath, 0, -1) . '/value_type/arguments/data/options';

        $price = $this->arrayManager->get($pricePath, $meta);
        if ($price) {
            $meta = $this->arrayManager->remove($pricePath, $meta);
            foreach ($price as $key => $item) {
                if ($item['value'] == ProductPriceOptionsInterface::VALUE_FIXED) {
                    unset($price[$key]);
                }
            }
            $meta = $this->arrayManager->merge(
                $this->arrayManager->slicePath($pricePath, 0, -1),
                $meta,
                ['options' => $price]
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

    /**
     * Modify Shipment Type configuration
     *
     * @param array $meta
     * @return array
     */
    private function modifyShipmentType(array $meta)
    {
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                static::CODE_SHIPMENT_TYPE,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'dataScope' => 'data.product.shipment_type',
                'validation' => [
                    'required-entry' => false
                ]
            ]
        );

        return $meta;
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
                                        'actionName' => 'processingAddChild',
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
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'dataScope' => 'data.bundle_options',
                        'bundleSelectionsName' => 'product_bundle_container.bundle_selections'
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
                                        'collapsible' => true,
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
                                                'componentType' => Container::NAME,
                                                'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows-grid',
                                                'sortOrder' => 50,
                                                'additionalClasses' => 'admin__field-wide',
                                                'template' => 'ui/dynamic-rows/templates/default',
                                                'provider' => 'product_form.product_form_data_source',
                                                'dataProvider' => '${ $.dataScope }' . '.bundle_button_proxy',
                                                'identificationDRProperty' => 'product_id',
                                                'identificationProperty' => 'product_id',
                                                'map' => [
                                                    'product_id' => 'entity_id',
                                                    'name' => 'name',
                                                    'sku' => 'sku',
                                                    'price' => 'price',
                                                    'delete' => '',
                                                    'selection_can_change_qty' => '',
                                                    'selection_id' => '',
                                                    'selection_price_type' => '',
                                                    'selection_price_value' => '',
                                                    'selection_qty' => '',
                                                ],
                                                'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                                                'source' => 'product'
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
                                'targetName' => 'product_form.product_form.' . static::CODE_BUNDLE_DATA . '.modal',
                                'actionName' => 'toggleModal'
                            ],
                            [
                                'targetName' => 'product_form.product_form.' . static::CODE_BUNDLE_DATA
                                    . '.modal.bundle_product_listing',
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
     * Get configuration for option title
     *
     * @return array
     */
    protected function getTitleConfiguration()
    {
        $result['title']['arguments']['data']['config'] = [
            'dataType' => Form\Element\DataType\Text::NAME,
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataScope' => $this->isDefaultStore() ? 'title' : 'default_title',
            'label' => $this->isDefaultStore() ? __('Option Title') : __('Default Title'),
            'sortOrder' => 10,
            'validation' => ['required-entry' => true],
        ];

        if (!$this->isDefaultStore()) {
            $result['store_title']['arguments']['data']['config'] = [
                'dataType' => Form\Element\DataType\Text::NAME,
                'formElement' => Form\Element\Input::NAME,
                'componentType' => Form\Field::NAME,
                'dataScope' => 'title',
                'label' => __('Store View Title'),
                'sortOrder' => 15,
                'validation' => ['required-entry' => true],
            ];
        }

        return $result;
    }

    /**
     * Get option info
     *
     * @return array
     */
    protected function getOptionInfo()
    {
        $result = [
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
                'type' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'componentType' => Form\Field::NAME,
                                'component' => 'Magento_Bundle/js/components/bundle-input-type',
                                'parentContainer' => 'product_bundle_container',
                                'selections' => 'bundle_selections',
                                'isDefaultIndex' => 'is_default',
                                'userDefinedIndex' => 'selection_can_change_qty',
                                'dataScope' => 'type',
                                'label' => __('Input Type'),
                                'sortOrder' => 20,
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
                                'typeMap' => [
                                    'select' => 'radio',
                                    'radio' => 'radio',
                                    'checkbox' => 'checkbox',
                                    'multi' => 'checkbox'
                                ]
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

        return $this->arrayManager->merge('children', $result, $this->getTitleConfiguration());
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
                        'is_collection' => true
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
                                'formElement' => Form\Element\Checkbox::NAME,
                                'componentType' => Form\Field::NAME,
                                'component' => 'Magento_Bundle/js/components/bundle-checkbox',
                                'parentContainer' => 'product_bundle_container',
                                'parentSelections' => 'bundle_selections',
                                'changer' => 'option_info.type',
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'label' => __('Is Default'),
                                'dataScope' => 'is_default',
                                'prefer' => 'radio',
                                'value' => '0',
                                'sortOrder' => 50,
                                'valueMap' => ['false' => '0', 'true' => '1']
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
                                    'validate-greater-than-zero' => true
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

    /**
     * Check that store is default
     *
     * @return bool
     */
    protected function isDefaultStore()
    {
        return $this->locator->getProduct()->getStoreId() == 0;
    }
}
