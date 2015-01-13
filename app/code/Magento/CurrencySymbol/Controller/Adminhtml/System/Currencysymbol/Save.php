<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

class Save extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol
{
    /**
     * Save custom Currency symbol
     *
     * @return void
     */
    public function execute()
    {
        $symbolsDataArray = $this->getRequest()->getParam('custom_currency_symbol', null);
        if (is_array($symbolsDataArray)) {
            foreach ($symbolsDataArray as &$symbolsData) {
                /** @var $filterManager \Magento\Framework\Filter\FilterManager */
                $filterManager = $this->_objectManager->get('Magento\Framework\Filter\FilterManager');
                $symbolsData = $filterManager->stripTags($symbolsData);
            }
        }

        try {
            $this->_objectManager->create(
                'Magento\CurrencySymbol\Model\System\Currencysymbol'
            )->setCurrencySymbolsData(
                $symbolsDataArray
            );
            $this->messageManager->addSuccess(__('The custom currency symbols were applied.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
