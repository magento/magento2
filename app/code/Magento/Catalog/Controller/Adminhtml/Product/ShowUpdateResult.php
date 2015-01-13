<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

class ShowUpdateResult extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Show item update result from updateAction
     * in Wishlist and Cart controllers.
     *
     * @return bool
     */
    public function execute()
    {
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        if ($session->hasCompositeProductResult()
            && $session->getCompositeProductResult() instanceof \Magento\Framework\Object
        ) {
            $this->_objectManager->get('Magento\Catalog\Helper\Product\Composite')
                ->renderUpdateResult($session->getCompositeProductResult());
            $session->unsCompositeProductResult();
        } else {
            $session->unsCompositeProductResult();
            return false;
        }
    }
}
