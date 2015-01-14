<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

class Reset extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol
{
    /**
     * Resets custom Currency symbol for all store views, websites and default value
     *
     * @return void
     */
    public function execute()
    {
        $this->_objectManager->create('Magento\CurrencySymbol\Model\System\Currencysymbol')->resetValues();
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
