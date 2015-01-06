<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Controller\Onepage;

use Magento\Checkout\Controller\Onepage;

class Review extends Onepage
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
