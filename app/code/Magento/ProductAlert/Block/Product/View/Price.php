<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Product\View;

/**
 * Product view price
 *
 * @api
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
        if (!$this->_helper->isPriceAlertAllowed()
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
