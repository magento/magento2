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
namespace Magento\ProductAlert\Controller\Unsubscribe;

class Price extends \Magento\ProductAlert\Controller\Unsubscribe
{
    /**
     * @return void
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product');

        if (!$productId) {
            $this->_redirect('');
            return;
        }
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            /* @var $product \Magento\Catalog\Model\Product */
            $this->messageManager->addError(__('We can\'t find the product.'));
            $this->_redirect('customer/account/');
            return;
        }

        try {
            $model = $this->_objectManager->create(
                'Magento\ProductAlert\Model\Price'
            )->setCustomerId(
                $this->_customerSession->getCustomerId()
            )->setProductId(
                $product->getId()
            )->setWebsiteId(
                $this->_objectManager->get('Magento\Framework\StoreManagerInterface')->getStore()->getWebsiteId()
            )->loadByParam();
            if ($model->getId()) {
                $model->delete();
            }

            $this->messageManager->addSuccess(__('You deleted the alert subscription.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->getResponse()->setRedirect($product->getProductUrl());
    }
}
