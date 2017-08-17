<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Review\Controller\Adminhtml\Product\NewAction
 *
 */
class NewAction extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Reviews'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Review'));
        $resultPage->addContent($resultPage->getLayout()->createBlock(\Magento\Review\Block\Adminhtml\Add::class));
        $resultPage->addContent($resultPage->getLayout()->createBlock(
            \Magento\Review\Block\Adminhtml\Product\Grid::class
        ));
        return $resultPage;
    }
}
