<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $dirtyRules = $this->_objectManager->create(\Magento\CatalogRule\Model\Flag::class)->loadSelf();
        $this->_eventManager->dispatch(
            'catalogrule_dirty_notice',
            ['dirty_rules' => $dirtyRules, 'message' => $this->getDirtyRulesNoticeMessage()]
        );
        $this->_initAction()->_addBreadcrumb(__('Catalog'), __('Catalog'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Catalog Price Rule'));
        $this->_view->renderLayout();
    }
}
