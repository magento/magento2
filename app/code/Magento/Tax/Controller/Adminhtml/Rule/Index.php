<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

/**
 * Class \Magento\Tax\Controller\Adminhtml\Rule\Index
 *
 */
class Index extends \Magento\Tax\Controller\Adminhtml\Rule
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->initResultPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Tax Rules'));
        return $resultPage;
    }
}
