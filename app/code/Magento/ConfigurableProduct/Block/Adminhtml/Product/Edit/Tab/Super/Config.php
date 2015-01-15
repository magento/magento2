<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Adminhtml catalog super product configurable tab
 */
class Config extends Widget implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/super/config.phtml';

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param Configurable $configurableType
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        Configurable $configurableType,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        $this->_configurableType = $configurableType;
        $this->_coreRegistry = $coreRegistry;
        $this->_catalogData = $catalogData;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Initialize block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setProductId($this->getRequest()->getParam('id'));

        $this->setId('config_super_product');
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Retrieve Tab class (for loading)
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Check block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return (bool)$this->getProduct()->getCompositeReadonly();
    }

    /**
     * Check whether attributes of configurable products can be editable
     *
     * @return bool
     */
    public function isAttributesConfigurationReadonly()
    {
        return (bool)$this->getProduct()->getAttributesConfigurationReadonly();
    }

    /**
     * Check whether prices of configurable products can be editable
     *
     * @return bool
     */
    public function isAttributesPricesReadonly()
    {
        return $this->getProduct()->getAttributesConfigurationReadonly() ||
            $this->_catalogData->isPriceGlobal() && $this->isReadonly();
    }

    /**
     * Prepare Layout data
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'create_empty',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Create Empty'), 'class' => 'add', 'onclick' => 'superProduct.createEmptyProduct()']
        );
        $this->addChild(
            'super_settings',
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Settings'
        );

        $this->addChild(
            'generate',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Generate Variations'),
                'class' => 'generate',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'generate',
                            'target' => '#product-variations-matrix',
                            'eventData' => [
                                'url' => $this->getUrl(
                                    'catalog/product_generateVariations/index',
                                    ['_current' => true]
                                ),
                            ],
                        ],
                    ],
                    'action' => 'generate',
                ]
            ]
        );
        $this->addChild(
            'add_attribute',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Create New Variation Set'),
                'class' => 'new-variation-set',
                'data_attribute' => [
                    'mage-init' => [
                        'configurableAttribute' => [
                            'url' => $this->getUrl(
                                'catalog/product_attribute/new',
                                [
                                    'store' => $this->getProduct()->getStoreId(),
                                    'product_tab' => 'variations',
                                    'popup' => 1,
                                    '_query' => [
                                        'attribute' => [
                                            'is_global' => 1,
                                            'frontend_input' => 'select',
                                            'is_configurable' => 1,
                                        ],
                                    ]
                                ]
                            ),
                        ],
                    ],
                ]
            ]
        );
        $this->addChild(
            'add_option',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Add Option'),
                'class' => 'action- scalable add',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'add-option']],
                    'action' => 'add-option',
                ]
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = (array)$this->_configurableType->getConfigurableAttributesAsArray($this->getProduct());
            $productData = (array)$this->getRequest()->getParam('product');
            if (isset($productData['configurable_attributes_data'])) {
                $configurableData = $productData['configurable_attributes_data'];
                foreach ($attributes as $key => $attribute) {
                    if (isset($configurableData[$key])) {
                        $attributes[$key] = array_replace_recursive($attribute, $configurableData[$key]);
                        $attributes[$key]['values'] = array_merge(
                            isset($attribute['values']) ? $attribute['values'] : [],
                            isset($configurableData[$key]['values'])
                            ? array_filter($configurableData[$key]['values'])
                            : []
                        );
                    }
                }
            }

            foreach ($attributes as &$attribute) {
                if (isset($attribute['values']) && is_array($attribute['values'])) {
                    foreach ($attribute['values'] as &$attributeValue) {
                        if (!$this->getCanReadPrice()) {
                            $attributeValue['pricing_value'] = '';
                            $attributeValue['is_percent'] = 0;
                        }
                        $attributeValue['can_edit_price'] = $this->getCanEditPrice();
                        $attributeValue['can_read_price'] = $this->getCanReadPrice();
                    }
                }
            }
            $this->setData('attributes', $attributes);
        }
        return $this->getData('attributes');
    }

    /**
     * Retrieve Links in JSON format
     *
     * @return string
     */
    public function getLinksJson()
    {
        $products = $this->_configurableType->getUsedProducts($this->getProduct());
        if (!$products) {
            return '{}';
        }
        $data = [];
        foreach ($products as $product) {
            $data[$product->getId()] = $this->getConfigurableSettings($product);
        }
        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Retrieve configurable settings
     *
     * @param Product $product
     * @return array
     */
    public function getConfigurableSettings($product)
    {
        $data = [];
        $attributes = $this->_configurableType->getUsedProductAttributes($this->getProduct());
        foreach ($attributes as $attribute) {
            $data[] = [
                'attribute_id' => $attribute->getId(),
                'label' => $product->getAttributeText($attribute->getAttributeCode()),
                'value_index' => $product->getData($attribute->getAttributeCode()),
            ];
        }

        return $data;
    }

    /**
     * Retrieve Grid child HTML
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Retrieve Grid JavaScript object name
     *
     * @return string
     */
    public function getGridJsObject()
    {
        return $this->getChildBlock('grid')->getJsObjectName();
    }

    /**
     * Retrieve Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Associated Products');
    }

    /**
     * Retrieve Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Associated Products');
    }

    /**
     * Can show tab flag
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check is a hidden tab
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get list of used attributes
     *
     * @return array
     */
    public function getSelectedAttributes()
    {
        return $this->getProduct()->getTypeId() == Configurable::TYPE_CODE ? array_filter(
            $this->_configurableType->getUsedProductAttributes($this->getProduct())
        ) : [];
    }

    /**
     * Get parent tab code
     *
     * @return string
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * Get base application currency
     *
     * @return \Zend_Currency
     */
    public function getBaseCurrency()
    {
        return $this->_localeCurrency->getCurrency(
            $this->_scopeConfig->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default')
        );
    }
}
