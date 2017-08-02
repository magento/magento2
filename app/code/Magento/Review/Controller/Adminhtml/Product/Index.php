<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Review\Controller\Adminhtml\Product\Index
 *
 * @since 2.0.0
 */
class Index extends ProductController
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('ajax')) {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('reviewGrid');
            return $resultForward;
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Reviews'));
        $resultPage->getConfig()->getTitle()->prepend(__('Reviews'));
        $resultPage->addContent($resultPage->getLayout()->createBlock(\Magento\Review\Block\Adminhtml\Main::class));
        return $resultPage;
    }
}
