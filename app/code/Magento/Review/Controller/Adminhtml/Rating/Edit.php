<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Controller\Adminhtml\Rating;

class Edit extends \Magento\Review\Controller\Adminhtml\Rating
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initEnityId();
        $this->_view->loadLayout();

        $ratingModel = $this->_objectManager->create('Magento\Review\Model\Rating');
        if ($this->getRequest()->getParam('id')) {
            $ratingModel->load($this->getRequest()->getParam('id'));
        }

        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_ratings');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Ratings'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $ratingModel->getId() ? $ratingModel->getRatingCode() : __('New Rating')
        );
        $this->_addBreadcrumb(__('Manage Ratings'), __('Manage Ratings'));

        $this->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Rating\Edit')
        )->_addLeft(
            $this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Rating\Edit\Tabs')
        );
        $this->_view->renderLayout();
    }
}
