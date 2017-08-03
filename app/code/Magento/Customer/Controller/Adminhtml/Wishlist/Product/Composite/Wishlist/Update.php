<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;

use Exception;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist\Update
 *
 * @since 2.0.0
 */
class Update extends \Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist
{
    /**
     * IFrame handler for submitted configuration for wishlist item.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        // Update wishlist item
        $updateResult = new \Magento\Framework\DataObject();
        try {
            $this->_initData();

            $buyRequest = new \Magento\Framework\DataObject($this->getRequest()->getParams());

            $this->_wishlist->updateItem($this->_wishlistItem->getId(), $buyRequest)->save();

            $updateResult->setOk(true);
        } catch (Exception $e) {
            $updateResult->setError(true);
            $updateResult->setMessage($e->getMessage());
        }
        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setCompositeProductResult($updateResult);
        return $this->resultRedirectFactory->create()->setPath('catalog/product/showUpdateResult');
    }
}
