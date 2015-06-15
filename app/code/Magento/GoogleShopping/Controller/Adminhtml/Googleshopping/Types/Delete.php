<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Delete attribute set mapping
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('Magento\GoogleShopping\Model\Type');
            $model->load($id);
            if ($model->getTypeId()) {
                $model->delete();
            }
            $this->messageManager->addSuccess(__('Attribute set mapping was deleted'));
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__("We can't delete Attribute Set Mapping."));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/index', ['store' => $this->_getStore()->getId()]);
    }
}
