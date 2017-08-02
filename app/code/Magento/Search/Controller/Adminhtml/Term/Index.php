<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Search\Controller\Adminhtml\Term as TermController;

/**
 * Class \Magento\Search\Controller\Adminhtml\Term\Index
 *
 * @since 2.0.0
 */
class Index extends TermController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Search Terms'));
        $resultPage->addBreadcrumb(__('Search'), __('Search'));
        return $resultPage;
    }
}
