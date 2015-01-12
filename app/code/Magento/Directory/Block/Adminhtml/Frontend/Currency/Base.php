<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend model for base currency
 */
namespace Magento\Directory\Block\Adminhtml\Frontend\Currency;

class Base extends \Magento\Backend\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getRequest()->getParam('website') != '') {
            $priceScope = $this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($priceScope == \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL) {
                return '';
            }
        }
        return parent::render($element);
    }
}
