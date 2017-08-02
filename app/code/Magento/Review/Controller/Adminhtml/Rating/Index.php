<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Rating;

use Magento\Review\Controller\Adminhtml\Rating as RatingController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Review\Controller\Adminhtml\Rating\Index
 *
 * @since 2.0.0
 */
class Index extends RatingController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $this->initEnityId();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Review::catalog_reviews_ratings_ratings');
        $resultPage->addBreadcrumb(__('Manage Ratings'), __('Manage Ratings'));
        $resultPage->getConfig()->getTitle()->prepend(__('Ratings'));
        return $resultPage;
    }
}
