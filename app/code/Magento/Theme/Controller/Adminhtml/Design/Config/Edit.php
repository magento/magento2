<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config;

use Magento\Theme\Controller\Adminhtml\Index;

class Edit extends Index
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Theme Name'));
        return $resultPage;
    }
}
