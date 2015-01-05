<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ProductAlert\Block\Product\View;

/**
 * Product view price
 */
class Price extends \Magento\ProductAlert\Block\Product\View
{
    /**
     * Prepare price info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        if (
            !$this->_helper->isPriceAlertAllowed()
            || !$this->getProduct() ||
            false === $this->getProduct()->getCanShowPrice()
        ) {
            $template = '';
        } else {
            $this->setSignupUrl($this->_helper->getSaveUrl('price'));
        }
        return parent::setTemplate($template);
    }
}
