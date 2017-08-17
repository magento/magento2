<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;

/**
 * Class \Magento\Sitemap\Controller\Adminhtml\Sitemap\Index
 *
 */
class Index extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Site Map'));
        $this->_view->renderLayout();
    }
}
