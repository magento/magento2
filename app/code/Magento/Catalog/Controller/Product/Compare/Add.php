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
namespace Magento\Catalog\Controller\Product\Compare;

class Add extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * Add item to compare list
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            return;
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->_productFactory->create();
            $product->setStoreId($this->_storeManager->getStore()->getId())->load($productId);

            if ($product->getId()) {
                $this->_catalogProductCompareList->addProduct($product);
                $productName = $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName());
                $this->messageManager->addSuccess(__('You added product %1 to the comparison list.', $productName));
                $this->_eventManager->dispatch('catalog_product_compare_add_product', array('product' => $product));
            }

            $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
