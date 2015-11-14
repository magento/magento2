<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

class Chooser extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('attribute') == 'sku') {
            $type = 'Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku';
        }
        if (!empty($type)) {
            $block = $this->_view->getLayout()->createBlock($type);
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }
}
