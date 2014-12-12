<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ProductAlert\Block\Product\View;

/**
 * Recurring payment view stock
 */
class Stock extends \Magento\ProductAlert\Block\Product\View
{
    /**
     * Prepare stock info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        if (!$this->_helper->isStockAlertAllowed() || !$this->getProduct() || $this->getProduct()->isAvailable()) {
            $template = '';
        } else {
            $this->setSignupUrl($this->_helper->getSaveUrl('stock'));
        }
        return parent::setTemplate($template);
    }
}
