<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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
                    $this->setProductHelpers($productHelpers ? $productHelpers : array());
                }
            }
        }

        // Check whether we have helper for our product
        $productType = $product->getTypeId();
        if (isset($productHelpers[$productType])) {
            $helperName = $productHelpers[$productType];
        } else if (isset($productHelpers['default'])) {
            $helperName = $productHelpers['default'];
        } else {
            $helperName = 'Magento\Catalog\Helper\Product\Configuration';
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
        $params = array('max_length' => 55);
        return $this->_productConfig->getFormattedOptionValue($option, $params);
    }

    /**
     * Renders item product name and its configuration
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface|\Magento\Framework\Object $item
     * @return string
     */
    public function render(\Magento\Framework\Object $item)
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
        $html = '<div class="bundle-product-options">' . '<strong>' . $this->escapeHtml(
            $product->getName()
        ) . '</strong>' . '<dl>';
        foreach ($options as $option) {
            $formattedOption = $this->getFormattedOptionValue($option);
            $html .= '<dt>' . $this->escapeHtml($option['label']) . '</dt>';
            $html .= '<dd>' . $this->escapeHtml($formattedOption['value']) . '</dd>';
        }
        $html .= '</dl></div>';

        return $html;
    }
}
