<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Search\Controller\Adminhtml\Term;

class Index extends \Magento\Search\Controller\Adminhtml\Term
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
