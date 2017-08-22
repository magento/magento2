<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

/**
 * Class \Magento\Sitemap\Controller\Adminhtml\Sitemap\NewAction
 *
 */
class NewAction extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /**
     * Create new sitemap
     *
     * @return void
     */
    public function execute()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }
}
