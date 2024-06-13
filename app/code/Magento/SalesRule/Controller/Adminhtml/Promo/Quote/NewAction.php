<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote implements HttpGetActionInterface
{
    /**
     * New promo quote action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
