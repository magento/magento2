<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

/**
 * Edit action.
 */
class Edit extends ProductController implements HttpGetActionInterface
{
    /**
     * @var Review
     */
    private $review;

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Reviews'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Review'));
        $resultPage->addContent($resultPage->getLayout()->createBlock(\Magento\Review\Block\Adminhtml\Edit::class));
        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        if ($this->_authorization->isAllowed('Magento_Review::reviews_all')) {
            return true;
        }

        if (!$this->_authorization->isAllowed('Magento_Review::pending')) {
            return  false;
        }

        if ($this->getModel()->getStatusId() != Review::STATUS_PENDING) {
            $this->messageManager->addErrorMessage(
                __('Sorry, You have not permission to do this. The Review is not in Pending status.')
            );

            return false;
        }

        return true;
    }

    /**
     * Returns requested model.
     *
     * @return Review
     */
    private function getModel(): Review
    {
        if (!$this->review) {
            $this->review = $this->reviewFactory->create()
                ->load($this->getRequest()->getParam('id', false));
        }

        return $this->review;
    }
}
