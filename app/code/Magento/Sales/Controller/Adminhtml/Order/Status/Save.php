<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class Save extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * Save status form processing
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $isNew = $this->getRequest()->getParam('is_new');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $statusCode = $this->getRequest()->getParam('status');

            //filter tags in labels/status
            /** @var $filterManager \Magento\Framework\Filter\FilterManager */
            $filterManager = $this->_objectManager->get('Magento\Framework\Filter\FilterManager');
            if ($isNew) {
                $statusCode = $data['status'] = $filterManager->stripTags($data['status']);
            }            
            $data['label'] = $filterManager->stripTags($data['label']);            
            if (!isset($data['store_labels'])) {
                $data['store_labels'] = [];
            }

            foreach ($data['store_labels'] as &$label) {
                $label = $filterManager->stripTags($label);
            }

            $status = $this->_objectManager->create('Magento\Sales\Model\Order\Status')->load($statusCode);
            // check if status exist
            if ($isNew && $status->getStatus()) {
                $this->messageManager->addError(__('We found another order status with the same order status code.'));
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('sales/*/new');
            }

            $status->setData($data)->setStatus($statusCode);

            try {
                $status->save();
                $this->messageManager->addSuccess(__('You saved the order status.'));
                return $resultRedirect->setPath('sales/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('We can\'t add the order status right now.')
                );
            }
            $this->_getSession()->setFormData($data);
            if ($isNew) {
                return $resultRedirect->setPath('sales/*/new');
            } else {
                return $resultRedirect->setPath('sales/*/edit', ['status' => $this->getRequest()->getParam('status')]);
            }
        }
        return $resultRedirect->setPath('sales/*/');
    }
}
