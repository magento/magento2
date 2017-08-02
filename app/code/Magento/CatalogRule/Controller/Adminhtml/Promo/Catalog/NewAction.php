<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

/**
 * Class \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
