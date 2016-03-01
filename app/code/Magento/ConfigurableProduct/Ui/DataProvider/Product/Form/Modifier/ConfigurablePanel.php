<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Modal;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;

/**
 * Data provider for Configurable panel
 */
class ConfigurablePanel extends AbstractModifier
{
    const GROUP_CONFIGURABLE = 'configurable';
    const ASSOCIATED_PRODUCT_MODAL = 'configurable_associated_product_modal';
    const ASSOCIATED_PRODUCT_LISTING = 'configurable_associated_product_listing';
    const CONFIGURABLE_MATRIX = 'configurable-matrix';

    /**
     * @var string
     */
    private static $groupContent = 'content';

    /**
     * @var int
     */
    private static $sortOrder = 30;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
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
                static::GROUP_CONFIGURABLE => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Configurations'),
                                'collapsible' => true,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    self::$groupContent,
                                    self::$sortOrder
                                ),
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren(),
                ],
                static::ASSOCIATED_PRODUCT_MODAL => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Modal::NAME,
                                'dataScope' => '',
                                'provider' => static::FORM_NAME . '.product_form_data_source',
                                'options' => [
                                    'title' => __('Select Associated Product'),
                                    'buttons' => [
                                        [
                                            'text' => __('Done'),
                                            'class' => 'action-primary',
                                            'actions' => [
                                                [
                                                    'targetName' => 'index=' . static::ASSOCIATED_PRODUCT_LISTING,
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
                        static::ASSOCIATED_PRODUCT_LISTING => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'autoRender' => false,
                                        'componentType' => 'insertListing',
                                        'dataScope' => static::ASSOCIATED_PRODUCT_LISTING,
                                        'externalProvider' => static::ASSOCIATED_PRODUCT_LISTING . '.'
                                            . static::ASSOCIATED_PRODUCT_LISTING . '_data_source',
                                        'selectionsProvider' => static::ASSOCIATED_PRODUCT_LISTING . '.'
                                            . static::ASSOCIATED_PRODUCT_LISTING . '.product_columns.ids',
                                        'ns' => static::ASSOCIATED_PRODUCT_LISTING,
                                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                        'realTimeLink' => true,
                                        'behaviourType' => 'simple',
                                        'externalFilterMode' => true,
                                        'currentProductId' => $this->locator->getProduct()->getId(),
                                        'dataLinks' => [
                                            'imports' => false,
                                            'exports' => true
                                        ],
//                                      'exports' => [
//                                          'currentProductId' => '${ $.externalProvider }:params.current_product_id'
//                                      ]
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren() {
        return [
            'configurable_products_button_set' => $this->getButtonSet(),
            self::CONFIGURABLE_MATRIX => $this->getGrid(),
        ];
    }

    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => __(
                            'Configurable products allow customers to choose options '
                            . '(Ex: shirt color). You need to create a simple product for each '
                            . 'configuration (Ex: a product for each color).'
                        ),
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'add_products_manually_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'displayAsLink' => true,
                                'actions' => [
                                    [
                                        'targetName' => 'ns=' . static::FORM_NAME . ', index='
                                            . static::ASSOCIATED_PRODUCT_MODAL,
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' => 'ns=' . static::ASSOCIATED_PRODUCT_LISTING
                                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
                                        'actionName' => 'render',
                                    ],
                                ],
                                'title' => __('Add Products Manually'),
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                ],
                'create_configurable_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' =>
                                            'product_form.product_form.configurableModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' =>
                                            'product_form.product_form.configurableModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Create Configurations'),
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getGrid()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => __('Current Variations'),
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_ConfigurableProduct/js/components/dynamic-rows-configurable',
                        'addButton' => false,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderFromWizard' => 'variations',
                        'map' => [
                            'id' => 'entity_id',
                            'product_link' => 'product_link',
                            'name' => 'name',
                            'sku' => 'sku',
                            'price' => 'price_number',
                            'price_string' => 'price',
                            'price_currency' => 'price_currency',
                            'quantity_and_stock_status.qty' => 'qty',
                            'weight' => 'weight',
                        ],
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                        ],
                        'sortOrder' => 20,
                        'columnsHeader' => true,
                        'columnsHeaderAfterRender' => true,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     */
    protected function getRows()
    {
        return [
            'record' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'isTemplate' => true,
                            'is_collection' => true,
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'dataScope' => '',
                        ],
                    ],
                ],
                'children' => [
                    'name_container' => $this->getColumn(
                        'name',
                        __('Name'),
                        [],
                        ['dataScope' => 'product_link']
                    ),
                    'sku_container' => $this->getColumn('sku', __('SKU')),
                    'price_container' => $this->getColumn(
                        'price',
                        __('Price'),
                        [
                            'imports' => ['addbefore' => '${$.provider}:${$.parentScope}.price_currency'],
                            'validation' => ['validate-zero-or-greater' => true]
                        ],
                        ['dataScope' => 'price_string']
                    ),
                    'quantity_container' => $this->getColumn(
                        'quantity',
                        __('Quantity'),
                        ['dataScope' => 'quantity_and_stock_status.qty'],
                        ['dataScope' => 'quantity_and_stock_status.qty']
                    ),
                    'price_weight' => $this->getColumn('weight', __('Weight')),
                ],
            ],
        ];
    }

    /**
     * @param string $name
     * @param \Magento\Framework\Phrase $label
     * @param array $editConfig
     * @param array $textConfig
     * @return array
     */
    protected function getColumn(
        $name,
        \Magento\Framework\Phrase $label,
        $editConfig = [],
        $textConfig = []
    ) {
        $fieldEdit['arguments']['data']['config'] = [
            'component' => 'Magento_ConfigurableProduct/js/components/field-configurable',
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataScope' => $name,
            'fit' => true,
            'additionalClasses' => 'admin__field-small',
            'visibleIfCanEdit' => true,
            'imports' => [
                'parentComponentScope' => '${$.parentName}:dataScope',
            ],
        ];
        $fieldText['arguments']['data']['config'] = [
            'component' => 'Magento_ConfigurableProduct/js/components/field-configurable',
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-html',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => $name,
            'visibleIfCanEdit' => false,
            'imports' => [
                'parentComponentScope' => '${$.parentName}:dataScope',
            ],
        ];
        $fieldEdit['arguments']['data']['config'] = array_replace_recursive(
            $fieldEdit['arguments']['data']['config'],
            $editConfig
        );
        $fieldText['arguments']['data']['config'] = array_replace_recursive(
            $fieldText['arguments']['data']['config'],
            $textConfig
        );
        $container['arguments']['data']['config'] = [
            'additionalClasses' => 'admin__field',
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => $label,
            'dataScope' => '',
        ];
        $container['children'] = [
            $name . '_edit' => $fieldEdit,
            $name . '_text' => $fieldText,
        ];

        return $container;
    }
}