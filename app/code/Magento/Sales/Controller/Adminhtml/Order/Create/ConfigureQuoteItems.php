<?php
/**
 *
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
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use \Magento\Backend\App\Action;

class ConfigureQuoteItems extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Ajax handler to response configuration fieldset of composite product in quote items
     *
     * @return void
     */
    public function execute()
    {
        // Prepare data
        $configureResult = new \Magento\Framework\Object();
        try {
            $quoteItemId = (int)$this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                throw new \Magento\Framework\Model\Exception(__('Quote item id is not received.'));
            }

            $quoteItem = $this->_objectManager->create('Magento\Sales\Model\Quote\Item')->load($quoteItemId);
            if (!$quoteItem->getId()) {
                throw new \Magento\Framework\Model\Exception(__('Quote item is not loaded.'));
            }

            $configureResult->setOk(true);
            $optionCollection = $this->_objectManager->create(
                'Magento\Sales\Model\Quote\Item\Option'
            )->getCollection()->addItemFilter(
                array($quoteItemId)
            );
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            $sessionQuote = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());
        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        // Render page
        $this->_objectManager->get(
            'Magento\Catalog\Helper\Product\Composite'
        )->renderConfigureResult(
            $configureResult
        );
    }
}
