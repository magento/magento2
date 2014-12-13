<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Controller\Product;

class ListAjax extends \Magento\Review\Controller\Product
{
    /**
     * Show list of product's reviews
     *
     * @return void
     */
    public function execute()
    {
        $this->_initProduct();
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
