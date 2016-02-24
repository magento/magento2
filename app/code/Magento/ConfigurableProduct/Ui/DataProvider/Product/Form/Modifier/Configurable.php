<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\Product\Type;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Modal;
use Magento\Framework\UrlInterface;

/**
 * Data provider for Configurable products
 */
class Configurable extends AbstractModifier
{
    const GROUP_CONFIGURABLE = 'configurable';
    const ASSOCIATED_PRODUCT_MODAL = 'configurable_associated_product_modal';
    const ASSOCIATED_PRODUCT_LISTING = 'configurable_associated_product_listing';

    /**
     * @var array
     */
    private static $availableProductTypes = [
        ConfigurableType::TYPE_CODE,
        Type::TYPE_SIMPLE,
        Type::TYPE_VIRTUAL
    ];

    /**
     * @var string
     */
    private static $groupContent = 'content';

    /**
     * @var int
     */
    private static $sortOrder = 30;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

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
        //return $meta;

        if (in_array($this->locator->getProduct()->getTypeId(), self::$availableProductTypes)) {
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
//                                            'exports' => [
//                                                'currentProductId' => '${ $.externalProvider }:params.current_product_id'
//                                            ]
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
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren() {
        return [
            'configurable_products_button_set' => $this->getButtonSet(),
            'variations-matrix' => $this->getGrid(),
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
                        'map' => [
                            'id' => 'entity_id',
                            'name' => 'name',
                            'sku' => 'sku',
                            'price' => 'price',
                            'quantity_and_stock_status.qty' => 'qty'
                        ],
                        'links' => ['insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}'],
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
                    'name_container' => $this->getColumn('name', __('Name'), 'name'),
                    'sku_container' => $this->getColumn('sku', __('SKU'), 'sku'),
                    'price_container' => $this->getColumn('price', __('Price'), 'price'),
                    'quantity_container' => $this->getColumn('quantity', __('Quantity'), 'quantity_and_stock_status.qty'),
                ],
            ],
        ];
    }

    /**
     * @param string $name
     * @param \Magento\Framework\Phrase $label
     * @param string $dataScope
     * @return array
     */
    protected function getColumn($name, \Magento\Framework\Phrase $label, $dataScope = '')
    {
        $fieldEdit['arguments']['data']['config'] = [
            'component' => 'Magento_ConfigurableProduct/js/components/field-configurable',
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataScope' => $dataScope,
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
            'elementTmpl' => 'ui/dynamic-rows/cells/text',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => $dataScope,
            'visibleIfCanEdit' => false,
            'imports' => [
                'parentComponentScope' => '${$.parentName}:dataScope',
            ],
        ];
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