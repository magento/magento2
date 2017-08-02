<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete
 *
 */
class Delete extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('We can\'t delete the attribute.'));
                return $resultRedirect->setPath('catalog/*/');
            }

            try {
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the product attribute.'));
                return $resultRedirect->setPath('catalog/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    'catalog/*/edit',
                    ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                );
            }
        }
        $this->messageManager->addError(__('We can\'t find an attribute to delete.'));
        return $resultRedirect->setPath('catalog/*/');
    }
}
