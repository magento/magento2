<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class Index extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * List of all maps (items)
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->initPage()->addBreadcrumb(__('Attribute Maps'), __('Attribute Maps'));
        $resultPage->getConfig()->getTitle()->prepend(__('Google Content Attributes'));
        return $resultPage;
    }
}
