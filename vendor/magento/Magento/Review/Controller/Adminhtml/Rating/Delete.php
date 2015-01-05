<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Controller\Adminhtml\Rating;

class Delete extends \Magento\Review\Controller\Adminhtml\Rating
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = $this->_objectManager->create('Magento\Review\Model\Rating');
                /* @var $model \Magento\Review\Model\Rating */
                $model->load($this->getRequest()->getParam('id'))->delete();
                $this->messageManager->addSuccess(__('You deleted the rating.'));
                $this->_redirect('review/rating/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('review/rating/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->_redirect('review/rating/');
    }
}
