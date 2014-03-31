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
 * @category    Magento
 * @package     Magento_CurrencySymbol
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Currency Symbols Controller
 *
 * @category    Magento
 * @package     currencysymbol
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System;

class Currencysymbol extends \Magento\Backend\App\Action
{
    /**
     * Show Currency Symbols Management dialog
     *
     * @return void
     */
    public function indexAction()
    {
        // set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_CurrencySymbol::system_currency_symbols'
        )->_addBreadcrumb(
            __('System'),
            __('System')
        )->_addBreadcrumb(
            __('Manage Currency Rates'),
            __('Manage Currency Rates')
        );

        $this->_title->add(__('Currency Symbols'));
        $this->_view->renderLayout();
    }

    /**
     * Save custom Currency symbol
     *
     * @return void
     */
    public function saveAction()
    {
        $symbolsDataArray = $this->getRequest()->getParam('custom_currency_symbol', null);
        if (is_array($symbolsDataArray)) {
            foreach ($symbolsDataArray as &$symbolsData) {
                /** @var $filterManager \Magento\Filter\FilterManager */
                $filterManager = $this->_objectManager->get('Magento\Filter\FilterManager');
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

    /**
     * Resets custom Currency symbol for all store views, websites and default value
     *
     * @return void
     */
    public function resetAction()
    {
        $this->_objectManager->create('Magento\CurrencySymbol\Model\System\Currencysymbol')->resetValues();
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_CurrencySymbol::symbols');
    }
}
