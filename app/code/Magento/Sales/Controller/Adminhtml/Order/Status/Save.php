<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class Save extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * Save status form processing
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $isNew = $this->getRequest()->getParam('is_new');
        if ($data) {
            $statusCode = $this->getRequest()->getParam('status');

            //filter tags in labels/status
            /** @var $filterManager \Magento\Framework\Filter\FilterManager */
            $filterManager = $this->_objectManager->get('Magento\Framework\Filter\FilterManager');
            if ($isNew) {
                $statusCode = $data['status'] = $filterManager->stripTags($data['status']);
            }
            $data['label'] = $filterManager->stripTags($data['label']);
            foreach ($data['store_labels'] as &$label) {
                $label = $filterManager->stripTags($label);
            }

            $status = $this->_objectManager->create('Magento\Sales\Model\Order\Status')->load($statusCode);
            // check if status exist
            if ($isNew && $status->getStatus()) {
                $this->messageManager->addError(__('We found another order status with the same order status code.'));
                $this->_getSession()->setFormData($data);
                $this->_redirect('sales/*/new');
                return;
            }

            $status->setData($data)->setStatus($statusCode);

            try {
                $status->save();
                $this->messageManager->addSuccess(__('You have saved the order status.'));
                $this->_redirect('sales/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('We couldn\'t add your order status because something went wrong saving.')
                );
            }
            $this->_getSession()->setFormData($data);
            if ($isNew) {
                $this->_redirect('sales/*/new');
            } else {
                $this->_redirect('sales/*/edit', ['status' => $this->getRequest()->getParam('status')]);
            }
            return;
        }
        $this->_redirect('sales/*/');
    }
}
