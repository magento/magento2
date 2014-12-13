<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Controller\Cart;

class Delete extends \Magento\Checkout\Controller\Cart
{
    /**
     * Delete shopping cart item action
     *
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->cart->removeItem($id)->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We cannot remove the item.'));
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            }
        }
        $defaultUrl = $this->_objectManager->create('Magento\Framework\UrlInterface')->getUrl('*/*');
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($defaultUrl));
    }
}
