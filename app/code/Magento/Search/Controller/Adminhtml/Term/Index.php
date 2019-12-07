<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Search\Controller\Adminhtml\Term as TermController;

class Index extends TermController implements HttpGetActionInterface
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Search Terms'));
        $resultPage->addBreadcrumb(__('Search'), __('Search'));
        return $resultPage;
    }
}
