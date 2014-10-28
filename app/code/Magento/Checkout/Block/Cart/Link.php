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
namespace Magento\Checkout\Block\Cart;

/**
 * "My Cart" link
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Checkout\Helper\Cart $cartHelper,
        array $data = array()
    ) {
        $this->_cartHelper = $cartHelper;
        parent::__construct($context, $data);
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_createLabel($this->_getItemCount());
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_moduleManager->isOutputEnabled('Magento_Checkout')) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Count items in cart
     *
     * @return int
     */
    protected function _getItemCount()
    {
        $count = $this->getSummaryQty();
        return $count ? $count : $this->_cartHelper->getSummaryCount();
    }

    /**
     * Create link label based on cart item quantity
     *
     * @param int $count
     * @return string
     */
    protected function _createLabel($count)
    {
        if ($count == 1) {
            return __('My Cart (1 item)');
        } elseif ($count > 0) {
            return __('My Cart (%1 items)', $count);
        } else {
            return __('My Cart');
        }
    }
}
