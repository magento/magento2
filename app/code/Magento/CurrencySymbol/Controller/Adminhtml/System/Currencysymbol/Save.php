<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class Save
 */
class Save extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol implements HttpPostActionInterface
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
                $filterManager = $this->_objectManager->get(\Magento\Framework\Filter\FilterManager::class);
                $symbolsData = $filterManager->stripTags($symbolsData);
            }
        }

        try {
            $this->_objectManager->create(\Magento\CurrencySymbol\Model\System\Currencysymbol::class)
                ->setCurrencySymbolsData($symbolsDataArray);
            $this->messageManager->addSuccessMessage(__('You applied the custom currency symbols.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
