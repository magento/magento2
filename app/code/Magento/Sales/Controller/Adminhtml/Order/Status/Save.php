<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Sales\Model\Order\Status;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Controller\Adminhtml\Order\Status as StatusAction;

class Save extends StatusAction implements HttpPostActionInterface
{
    /**
     * Save status form processing
     *
     * @return Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $isNew = $this->getRequest()->getParam('is_new');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $statusCode = $this->getRequest()->getParam('status');

            //filter tags in labels/status
            /** @var $filterManager FilterManager */
            $filterManager = $this->_objectManager->get(FilterManager::class);
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

            $status = $this->_objectManager->create(Status::class)->load($statusCode);
            // check if status exist
            if ($isNew && $status->getStatus()) {
                $this->messageManager
                    ->addErrorMessage(__('We found another order status with the same order status code.'));
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('sales/*/new');
            }

            $status->setData($data)->setStatus($statusCode);

            try {
                $status->save();
                $this->messageManager->addSuccessMessage(__('You saved the order status.'));
                return $resultRedirect->setPath('sales/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add the order status right now.')
                );
            }
            $this->_getSession()->setFormData($data);
            return $this->getRedirect($resultRedirect, $isNew);
        }
        return $resultRedirect->setPath('sales/*/');
    }

    /**
     * @param Redirect $resultRedirect
     * @param bool $isNew
     * @return Redirect
     */
    private function getRedirect(Redirect $resultRedirect, $isNew)
    {
        if ($isNew) {
            return $resultRedirect->setPath('sales/*/new');
        } else {
            return $resultRedirect->setPath('sales/*/edit', ['status' => $this->getRequest()->getParam('status')]);
        }
    }
}
