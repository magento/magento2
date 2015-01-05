<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
