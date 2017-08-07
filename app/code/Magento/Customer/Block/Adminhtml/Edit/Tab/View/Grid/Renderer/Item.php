<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer;

use Magento\Catalog\Model\Product;

/**
 * Adminhtml customers wishlist grid item renderer for name/options cell
 */
class Item extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration|null
     */
    protected $_productConfig = null;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $_productConfigPool;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $productConfigPool
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Catalog\Helper\Product\ConfigurationPool $productConfigPool,
        array $data = []
    ) {
        $this->_productConfigPool = $productConfigPool;
        $this->_productConfig = $productConfig;
        parent::__construct($context, $data);
    }

    /**
     * Returns helper for product type
     *
     * @param Product $product
     * @return \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
     */
    protected function _getProductHelper($product)
    {
        // Retrieve whole array of renderers
        $productHelpers = $this->getProductHelpers();
        if (!is_array($productHelpers)) {
            $column = $this->getColumn();
            if ($column) {
                $grid = $column->getGrid();
                if ($grid) {
                    $productHelpers = $grid->getProductConfigurationHelpers();
                    $this->setProductHelpers($productHelpers ? $productHelpers : []);
                }
            }
        }

        // Check whether we have helper for our product
        $productType = $product->getTypeId();
        if (isset($productHelpers[$productType])) {
            $helperName = $productHelpers[$productType];
        } elseif (isset($productHelpers['default'])) {
            $helperName = $productHelpers['default'];
        } else {
            $helperName = \Magento\Catalog\Helper\Product\Configuration::class;
        }

        return $this->_productConfigPool->get($helperName);
    }

    /**
     * Returns product associated with this block
     *
     * @return string
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Returns list of options and their values for product configuration
     *
     * @return array
     */
    protected function getOptionList()
    {
        $item = $this->getItem();
        $product = $item->getProduct();
        $helper = $this->_getProductHelper($product);
        return $helper->getOptions($item);
    }

    /**
     * Returns formatted option value for an item
     *
     * @param \Magento\Wishlist\Model\Item\Option $option
     * @return array
     */
    protected function getFormattedOptionValue($option)
    {
        $params = ['max_length' => 55];
        return $this->_productConfig->getFormattedOptionValue($option, $params);
    }

    /**
     * Renders item product name and its configuration
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface|\Magento\Framework\DataObject $item
     * @return string
     */
    public function render(\Magento\Framework\DataObject $item)
    {
        $this->setItem($item);
        $product = $this->getProduct();
        $options = $this->getOptionList();
        return $options ? $this->_renderItemOptions($product, $options) : $this->escapeHtml($product->getName());
    }

    /**
     * Render product item with options
     *
     * @param Product $product
     * @param array $options
     * @return string
     */
    protected function _renderItemOptions(Product $product, array $options)
    {
        $html = '<div class="product-title">' . $this->escapeHtml(
            $product->getName()
        ) . '</div>' . '<dl class="item-options">';
        foreach ($options as $option) {
            $formattedOption = $this->getFormattedOptionValue($option);
            $html .= '<dt>' . $this->escapeHtml($option['label']) . '</dt>';
            $html .= '<dd>' . $formattedOption['value'] . '</dd>';
        }
        $html .= '</dl>';

        return $html;
    }
}
