<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier;

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
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CurrencyInterface $localeCurrency
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->productLinkRepository = $productLinkRepository;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->status = $status;
        $this->localeCurrency = $localeCurrency;
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
            /** @var \Magento\Framework\Currency $currency */
            $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());
            $data[$product->getId()]['links'][self::LINK_TYPE] = [];
            foreach ($this->productLinkRepository->getList($product) as $linkItem) {
                if ($linkItem->getLinkType() !== self::LINK_TYPE) {
                    continue;
                }
                /** @var \Magento\Catalog\Api\Data\ProductInterface $linkedProduct */
                $linkedProduct = $this->productRepository->get($linkItem->getLinkedProductSku(), false, $storeId);
                $data[$modelId]['links'][self::LINK_TYPE][] = [
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
            $data[$modelId][self::DATA_SOURCE_DEFAULT]['current_store_id'] = $storeId;
        }
        return $data;
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
            'grouped_products_button_set' => $this->getButtonSet(),
            'grouped_products_modal' => $this->getModal(),
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
                        'provider' => 'product_form.product_form_data_source',
                        'options' => [
                            'title' => __('Add Products to Group'),
                            'buttons' => [
                                [
                                    'text' => __('Cancel'),
                                    'class' => 'action-secondary',
                                    'actions' => ['closeModal']
                                ],
                                [
                                    'text' => __('Add Selected Products'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = grouped_product_listing',
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
            'children' => ['grouped_product_listing' => $this->getListing()],
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
                        'dataScope' => 'grouped_product_listing',
                        'externalProvider' => 'grouped_product_listing.grouped_product_listing_data_source',
                        'selectionsProvider' => 'grouped_product_listing.grouped_product_listing.product_columns.ids',
                        'ns' => 'grouped_product_listing',
                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                        'realTimeLink' => true,
                        'provider' => 'product_form.product_form_data_source',
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
                                            'product_form.product_form.'
                                            . static::GROUP_GROUPED
                                            . '.grouped_products_modal',
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' =>
                                            'product_form.product_form.'
                                            . static::GROUP_GROUPED
                                            . '.grouped_products_modal.grouped_product_listing',
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
                        'dataProvider' => 'grouped_product_listing',
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
                'children' => [
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
                    'name' => $this->getTextColumn('name', false, 'Name', 30),
                    'attribute_set' => $this->getTextColumn('attribute_set', false, 'Attribute Set', 40),
                    'status' => $this->getTextColumn('status', true, 'Status', 50),
                    'sku' => $this->getTextColumn('sku', false, 'SKU', 60),
                    'price' => $this->getTextColumn('price', true, 'Price', 70),
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
                ],
            ],
        ];
    }

    /**
     * Returns text column configuration for the dynamic grid
     *
     * @param string $dataScope
     * @param boolean $fit
     * @param string $label
     * @param int $sortOrder
     * @return array
     */
    protected function getTextColumn($dataScope, $fit, $label, $sortOrder)
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
                        'label' => new Phrase($label),
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
        return $column;
    }
}
