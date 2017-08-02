<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

/**
 * Class \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Chooser
 *
 * @since 2.0.0
 */
class Chooser extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('attribute') == 'sku') {
            $type = \Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku::class;
        }
        if (!empty($type)) {
            $block = $this->_view->getLayout()->createBlock($type);
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }
}
