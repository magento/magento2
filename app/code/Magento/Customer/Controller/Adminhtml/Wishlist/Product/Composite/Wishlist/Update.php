<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;

use Exception;

class Update extends \Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist
{
    /**
     * IFrame handler for submitted configuration for wishlist item.
     *
     * @return false
     */
    public function execute()
    {
        // Update wishlist item
        $updateResult = new \Magento\Framework\Object();
        try {
            $this->_initData();

            $buyRequest = new \Magento\Framework\Object($this->getRequest()->getParams());

            $this->_wishlist->updateItem($this->_wishlistItem->getId(), $buyRequest)->save();

            $updateResult->setOk(true);
        } catch (Exception $e) {
            $updateResult->setError(true);
            $updateResult->setMessage($e->getMessage());
        }
        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_objectManager->get('Magento\Backend\Model\Session')->setCompositeProductResult($updateResult);
        $this->_redirect('catalog/product/showUpdateResult');

        return false;
    }
}
