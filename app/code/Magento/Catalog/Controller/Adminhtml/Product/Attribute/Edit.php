<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute implements HttpGetActionInterface
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        /** @var $model \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $model = $this->_objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        )->setEntityTypeId(
            $this->_entityTypeId
        );
        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('catalog/*/');
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addErrorMessage(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('catalog/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getAttributeData(true);
        $presentation = $this->_objectManager->get(
            \Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation::class
        );
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->setFrontendInput($presentation->getPresentationInputType($model));
        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $id === null) {
            $model->addData($attributeData);
        }

        $this->_coreRegistry->register('entity_attribute', $model);

        $item = $id ? __('Edit Product Attribute') : __('New Product Attribute');

        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Product Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));
        return $resultPage;
    }
}
