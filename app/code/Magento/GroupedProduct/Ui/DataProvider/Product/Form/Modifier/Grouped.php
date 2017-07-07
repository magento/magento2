<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Form;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Locale\CurrencyInterface;

/**
 * Data provider for Grouped products
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped extends AbstractModifier
{
    const GROUP_GROUPED = 'grouped';
    const GROUP_CONTENT = 'content';
    const DATA_SCOPE_GROUPED = 'grouped';
    const SORT_ORDER = 20;
    const LINK_TYPE = 'associated';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var array
     */
    protected $uiComponentsConfig = [
        'button_set' => 'grouped_products_button_set',
        'modal' => 'grouped_products_modal',
        'listing' => 'grouped_product_listing',
        'form' => 'product_form',
    ];

    /**
     * @var string
     */
    private static $codeQuantityAndStockStatus = 'quantity_and_stock_status';

    /**
     * @var string
     */
    private static $codeQtyContainer = 'quantity_and_stock_status_qty';

    /**
     * @var string
     */
    private static $codeQty = 'qty';

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     * @param Status $status
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CurrencyInterface $localeCurrency
     * @param array $uiComponentsConfig
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CurrencyInterface $localeCurrency,
        array $uiComponentsConfig = []
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->productLinkRepository = $productLinkRepository;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->status = $status;
        $this->localeCurrency = $localeCurrency;
        $this->uiComponentsConfig = array_replace_recursive($this->uiComponentsConfig, $uiComponentsConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        if ($modelId) {
            $storeId = $this->locator->getStore()->getId();
            $data[$product->getId()]['links'][self::LINK_TYPE] = [];
            foreach ($this->productLinkRepository->getList($product) as $linkItem) {
                if ($linkItem->getLinkType() !== self::LINK_TYPE) {
                    continue;
                }
                /** @var \Magento\Catalog\Api\Data\ProductInterface $linkedProduct */
                $linkedProduct = $this->productRepository->get($linkItem->getLinkedProductSku(), false, $storeId);
                $data[$modelId]['links'][self::LINK_TYPE][] = $this->fillData($linkedProduct, $linkItem);
            }
            $data[$modelId][self::DATA_SOURCE_DEFAULT]['current_store_id'] = $storeId;
        }
        return $data;
    }

    /**
     * Fill data column
     *
     * @param ProductInterface $linkedProduct
     * @param ProductLinkInterface $linkItem
     * @return array
     */
    protected function fillData(ProductInterface $linkedProduct, ProductLinkInterface $linkItem)
    {
        /** @var \Magento\Framework\Currency $currency */
        $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());

        return [
            'id' => $linkedProduct->getId(),
            'name' => $linkedProduct->getName(),
            'sku' => $linkItem->getLinkedProductSku(),
            'price' => $currency->toCurrency(sprintf("%f", $linkedProduct->getPrice())),
            'qty' => $linkItem->getExtensionAttributes()->getQty(),
            'position' => $linkItem->getPosition(),
            'thumbnail' => $this->imageHelper->init($linkedProduct, 'product_listing_thumbnail')->getUrl(),
            'type_id' => $linkedProduct->getTypeId(),
            'status' => $this->status->getOptionText($linkedProduct->getStatus()),
            'attribute_set' => $this->attributeSetRepository
                ->get($linkedProduct->getAttributeSetId())
                ->getAttributeSetName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === GroupedProductType::TYPE_CODE) {
            $meta = array_replace_recursive(
                $meta,
                [
                    static::GROUP_GROUPED => [
                        'children' => $this->getChildren(),
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Grouped Products'),
                                    'collapsible' => true,
                                    'opened' => true,
                                    'componentType' => Form\Fieldset::NAME,
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $meta,
                                        static::GROUP_CONTENT,
                                        static::SORT_ORDER
                                    ),
                                ],
                            ],
                        ],
                    ],
                ]
            );
            $meta = $this->modifyQtyAndStockStatus($meta);
        }
        return $meta;
    }

    /**
     * Disable Qty and Stock status fields
     *
     * @param array $meta
     * @return array
     */
    protected function modifyQtyAndStockStatus(array $meta)
    {
        if ($groupCode = $this->getGroupCodeByField($meta, 'container_' . self::$codeQuantityAndStockStatus)) {
            $parentChildren = &$meta[$groupCode]['children'];
            if (!empty($parentChildren['container_' . self::$codeQuantityAndStockStatus])) {
                $parentChildren['container_' . self::$codeQuantityAndStockStatus] = array_replace_recursive(
                    $parentChildren['container_' . self::$codeQuantityAndStockStatus],
                    [
                        'children' => [
                            self::$codeQuantityAndStockStatus => [
                                'arguments' => [
                                    'data' => [
                                        'config' => ['disabled' => false],
                                    ],
                                ],
                            ],
                        ]
                    ]
                );
            }
        }
        if ($groupCode = $this->getGroupCodeByField($meta, self::$codeQtyContainer)) {
            $parentChildren = &$meta[$groupCode]['children'];
            if (!empty($parentChildren[self::$codeQtyContainer])) {
                $parentChildren[self::$codeQtyContainer] = array_replace_recursive(
                    $parentChildren[self::$codeQtyContainer],
                    [
                        'children' => [
                            self::$codeQty => [
                                'arguments' => [
                                    'data' => [
                                        'config' => ['disabled' => true],
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
     * Retrieve child meta configuration
     *
     * @return array
     */
    protected function getChildren()
    {
        $children = [
            $this->uiComponentsConfig['button_set'] => $this->getButtonSet(),
            $this->uiComponentsConfig['modal'] => $this->getModal(),
            self::LINK_TYPE => $this->getGrid(),
        ];
        return $children;
    }

    /**
     * Returns Modal configuration
     *
     * @return array
     */
    protected function getModal()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'provider' =>
                            $this->uiComponentsConfig['form']
                            . '.'
                            . $this->uiComponentsConfig['form']
                            . '_data_source',
                        'options' => [
                            'title' => __('Add Products to Group'),
                            'buttons' => [
                                [
                                    'text' => __('Cancel'),
                                    'actions' => ['closeModal']
                                ],
                                [
                                    'text' => __('Add Selected Products'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . $this->uiComponentsConfig['listing'],
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
            'children' => [$this->uiComponentsConfig['listing'] => $this->getListing()],
        ];
    }

    /**
     * Returns Listing configuration
     *
     * @return array
     */
    protected function getListing()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'autoRender' => false,
                        'componentType' => 'insertListing',
                        'dataScope' => $this->uiComponentsConfig['listing'],
                        'externalProvider' =>
                            $this->uiComponentsConfig['listing']
                            . '.'
                            . $this->uiComponentsConfig['listing']
                            . '_data_source',
                        'selectionsProvider' =>
                            $this->uiComponentsConfig['listing']
                            . '.'
                            . $this->uiComponentsConfig['listing']
                            . '.product_columns.ids',
                        'ns' => $this->uiComponentsConfig['listing'],
                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                        'realTimeLink' => true,
                        'provider' =>
                            $this->uiComponentsConfig['form']
                            . '.'
                            . $this->uiComponentsConfig['form']
                            . '_data_source',
                        'dataLinks' => ['imports' => false, 'exports' => true],
                        'behaviourType' => 'simple',
                        'externalFilterMode' => true,
                        'imports' => [
                            'storeId' => '${ $.provider }:data.product.current_store_id',
                        ],
                        'exports' => [
                            'storeId' => '${ $.externalProvider }:params.current_store_id',
                        ],
                    ],
                ],
            ],
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
                            'A grouped product is made up of multiple, standalone products that are presented '
                            . 'as a group. You can offer variations of a single product, or group them by season or '
                            . 'theme to create a coordinated set. Each product can be purchased separately, '
                            . 'or as part of the group.'
                        ),
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'grouped_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' =>
                                            $this->uiComponentsConfig['form'] . '.' . $this->uiComponentsConfig['form']
                                            . '.'
                                            . static::GROUP_GROUPED
                                            . '.'
                                            . $this->uiComponentsConfig['modal'],
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' =>
                                            $this->uiComponentsConfig['form'] . '.' . $this->uiComponentsConfig['form']
                                            . '.'
                                            . static::GROUP_GROUPED
                                            . '.'
                                            . $this->uiComponentsConfig['modal']
                                            . '.'
                                            . $this->uiComponentsConfig['listing'],
                                        'actionName' => 'render',
                                    ],
                                ],
                                'title' => __('Add Products to Group'),
                                'provider' => null,
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
        $grid = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'addButton' => false,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data.links',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $this->uiComponentsConfig['listing'],
                        'map' => [
                            'id' => 'entity_id',
                            'name' => 'name',
                            'sku' => 'sku',
                            'price' => 'price',
                            'status' => 'status_text',
                            'attribute_set' => 'attribute_set_text',
                            'thumbnail' => 'thumbnail_src',
                        ],
                        'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                        'sortOrder' => 20,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
        return $grid;
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
                            'componentType' => 'container',
                            'isTemplate' => true,
                            'is_collection' => true,
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'dataScope' => '',
                        ],
                    ],
                ],
                'children' => $this->fillMeta(),
            ],
        ];
    }

    /**
     * Fill meta columns
     *
     * @return array
     */
    protected function fillMeta()
    {
        return [
            'id' => $this->getTextColumn('id', true, __('ID'), 10),
            'thumbnail' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Form\Field::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'dataScope' => 'thumbnail',
                            'fit' => true,
                            'label' => __('Thumbnail'),
                            'sortOrder' => 20,
                        ],
                    ],
                ],
            ],
            'name' => $this->getTextColumn('name', false, __('Name'), 30),
            'attribute_set' => $this->getTextColumn('attribute_set', false, __('Attribute Set'), 40),
            'status' => $this->getTextColumn('status', true, __('Status'), 50),
            'sku' => $this->getTextColumn('sku', false, __('SKU'), 60),
            'price' => $this->getTextColumn('price', true, __('Price'), 70),
            'qty' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'qty',
                            'label' => __('Default Quantity'),
                            'fit' => true,
                            'additionalClasses' => 'admin__field-small',
                            'sortOrder' => 80,
                            'validation' => [
                                'validate-number' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 90,
                            'fit' => true,
                        ],
                    ],
                ],
            ],
            'position' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'position',
                            'sortOrder' => 100,
                            'visible' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns text column configuration for the dynamic grid
     *
     * @param string $dataScope
     * @param bool $fit
     * @param Phrase $label
     * @param int $sortOrder
     * @return array
     */
    protected function getTextColumn($dataScope, $fit, Phrase $label, $sortOrder)
    {
        $column = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'dataScope' => $dataScope,
                        'fit' => $fit,
                        'label' => $label,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
        return $column;
    }
}
