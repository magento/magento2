<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
