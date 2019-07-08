<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Modal;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var string
     */
    private $formName;

    /**
     * @var string
     */
    private $dataScopeName;

    /**
     * @var string
     */
    private $dataSourceName;

    /**
     * @var string
     */
    private $associatedListingPrefix;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param string $formName
     * @param string $dataScopeName
     * @param string $dataSourceName
     * @param string $associatedListingPrefix
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        $formName,
        $dataScopeName,
        $dataSourceName,
        $associatedListingPrefix = ''
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->formName = $formName;
        $this->dataScopeName = $dataScopeName;
        $this->dataSourceName = $dataSourceName;
        $this->associatedListingPrefix = $associatedListingPrefix;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
                                'provider' => $this->dataSourceName,
                                'options' => [
                                    'title' => __('Select Associated Product'),
                                    'buttons' => [
                                        [
                                            'text' => __('Done'),
                                            'class' => 'action-primary',
                                            'actions' => [
                                                [
                                                    'targetName' => 'ns= ' . $this->associatedListingPrefix
                                                        . static::ASSOCIATED_PRODUCT_LISTING
                                                        . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
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
                        'information-block1' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Container::NAME,
                                        'component' => 'Magento_Ui/js/form/components/html',
                                        'additionalClasses' => 'message message-notice',
                                        'content' => __(
                                            'Choose a new product to delete and replace'
                                            . ' the current product configuration.'
                                        ),
                                        'imports' => [
                                            'visible' => '!ns = ${ $.ns }, index = '
                                                . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'information-block2' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Container::NAME,
                                        'component' => 'Magento_Ui/js/form/components/html',
                                        'additionalClasses' => 'message message-notice',
                                        'content' => __(
                                            'For better results, add attributes and attribute values to your products.'
                                        ),
                                        'imports' => [
                                            'visible' => 'ns = ${ $.ns }, index = '
                                                . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        static::ASSOCIATED_PRODUCT_LISTING => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'autoRender' => false,
                                        'componentType' => 'insertListing',
                                        'component' => 'Magento_ConfigurableProduct/js'
                                            . '/components/associated-product-insert-listing',
                                        'dataScope' => $this->associatedListingPrefix
                                            . static::ASSOCIATED_PRODUCT_LISTING,
                                        'externalProvider' => $this->associatedListingPrefix
                                            . static::ASSOCIATED_PRODUCT_LISTING . '.data_source',
                                        'selectionsProvider' => $this->associatedListingPrefix
                                            . static::ASSOCIATED_PRODUCT_LISTING . '.'
                                            . $this->associatedListingPrefix
                                            . static::ASSOCIATED_PRODUCT_LISTING . '.product_columns.ids',
                                        'ns' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
                                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                        'realTimeLink' => true,
                                        'behaviourType' => 'simple',
                                        'externalFilterMode' => false,
                                        'currentProductId' => $this->locator->getProduct()->getId(),
                                        'dataLinks' => [
                                            'imports' => false,
                                            'exports' => true
                                        ],
                                        'changeProductProvider' => 'change_product',
                                        'productsProvider' => $this->associatedListingPrefix
                                            . 'configurable_associated_product_listing.data_source',
                                        'productsColumns' => $this->associatedListingPrefix
                                            . 'configurable_associated_product_listing'
                                            . '.' . $this->associatedListingPrefix
                                            . 'configurable_associated_product_listing.product_columns',
                                        'productsMassAction' => $this->associatedListingPrefix
                                            . 'configurable_associated_product_listing'
                                            . '.' . $this->associatedListingPrefix
                                            . 'configurable_associated_product_listing.product_columns.ids',
                                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                                            . static::ASSOCIATED_PRODUCT_MODAL,
                                        'productsFilters' => $this->associatedListingPrefix
                                            . 'configurable_associated_product_listing'
                                            . '.' . $this->associatedListingPrefix
                                            . 'configurable_associated_product_listing.listing_top.listing_filters',
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
    protected function getPanelChildren()
    {
        return [
            'configurable_products_button_set' => $this->getButtonSet(),
            'configurable-matrix' => $this->getGrid(),
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
                        'component' => 'Magento_ConfigurableProduct/js/components/container-configurable-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            'Configurable products allow customers to choose options '
                            . '(Ex: shirt color). You need to create a simple product for each '
                            . 'configuration (Ex: a product for each color).'
                        ),
                        'content2' => __(
                            'Configurations cannot be created for a standard product with downloadable files. '
                            . 'To create configurations, first remove all downloadable files.'
                        ),
                        'template' => 'ui/form/components/complex',
                        'createConfigurableButton' => 'ns = ${ $.ns }, index = create_configurable_products_button',
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
                                        'targetName' => 'ns=' . $this->formName . ', index='
                                            . static::ASSOCIATED_PRODUCT_MODAL,
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' => 'ns=' . $this->associatedListingPrefix
                                            . static::ASSOCIATED_PRODUCT_LISTING
                                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
                                        'actionName' => 'showGridAssignProduct',
                                    ],
                                ],
                                'title' => __('Add Products Manually'),
                                'sortOrder' => 10,
                                'imports' => [
                                    'visible' => 'ns = ${ $.ns }, index = '
                                        . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isShowAddProductButton',
                                ],
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
                                        'targetName' => $this->dataScopeName . '.configurableModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' => $this->dataScopeName . '.configurableModal',
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
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'variations',
                        'map' => [
                            'id' => 'entity_id',
                            'product_link' => 'product_link',
                            'name' => 'name',
                            'sku' => 'sku',
                            'price' => 'price_number',
                            'price_string' => 'price',
                            'price_currency' => 'price_currency',
                            'qty' => 'qty',
                            'weight' => 'weight',
                            'thumbnail_image' => 'thumbnail_src',
                            'status' => 'status',
                            'attributes' => 'attributes',
                        ],
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                        ],
                        'sortOrder' => 20,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::ASSOCIATED_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
                            . static::ASSOCIATED_PRODUCT_LISTING
                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
                    'thumbnail_image_container' => $this->getColumn(
                        'thumbnail_image',
                        __('Image'),
                        [
                            'fit' => true,
                            'formElement' => 'fileUploader',
                            'componentType' => 'fileUploader',
                            'component' => 'Magento_ConfigurableProduct/js/components/file-uploader',
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/file-uploader',
                            'fileInputName' => 'image',
                            'isMultipleFiles' => false,
                            'links' => [
                                'thumbnailUrl' => '${$.provider}:${$.parentScope}.thumbnail_image',
                                'thumbnail' => '${$.provider}:${$.parentScope}.thumbnail',
                                'smallImage' => '${$.provider}:${$.parentScope}.small_image',
                            ],
                            'uploaderConfig' => [
                                'url' => $this->urlBuilder->addSessionParam()->getUrl(
                                    'catalog/product_gallery/upload'
                                ),
                            ],
                            'dataScope' => 'image',
                        ],
                        [
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'fit' => true,
                            'sortOrder' => 0
                        ]
                    ),
                    'name_container' => $this->getColumn(
                        'name',
                        __('Name'),
                        [],
                        ['dataScope' => 'product_link']
                    ),
                    'sku_container' => $this->getColumn(
                        'sku',
                        __('SKU'),
                        [
                            'validation' => [
                                    'required-entry' => true,
                                    'max_text_length' => Sku::SKU_MAX_LENGTH,
                                ],
                        ],
                        [
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-sku',
                        ]
                    ),
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
                        ['dataScope' => 'qty'],
                        ['dataScope' => 'qty']
                    ),
                    'price_weight' => $this->getColumn('weight', __('Weight')),
                    'status' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/cell-status',
                                    'label' => __('Status'),
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                    'attributes' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'label' => __('Attributes'),
                                ],
                            ],
                        ],
                    ],
                    'actionsList' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'data-grid-actions-cell',
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/actions-list',
                                    'label' => __('Actions'),
                                    'fit' => true,
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get configuration of column
     *
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
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataScope' => $name,
            'fit' => true,
            'visibleIfCanEdit' => true,
            'imports' => [
                'visible' => '${$.provider}:${$.parentScope}.canEdit'
            ],
        ];
        $fieldText['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-html',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => $name,
            'visibleIfCanEdit' => false,
            'labelVisible' => false,
            'imports' => [
                'visible' => '!${$.provider}:${$.parentScope}.canEdit'
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
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => $label,
            'dataScope' => '',
            'showLabel' => false
        ];
        $container['children'] = [
            $name . '_edit' => $fieldEdit,
            $name . '_text' => $fieldText,
        ];

        return $container;
    }
}
