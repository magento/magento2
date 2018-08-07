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

class Save extends \Magento\Sales\Controller\Adminhtml\Order\Status implements HttpPostActionInterface
{
    /**
     * Save status form processing
     *
     * @return Redirect
     */
    public function execute()
    {
        $requestData = $this->gatherRequestData();
        if (!$requestData) {
            return $this->redirect('sales/*/');
        }

        $this->_getSession()->setFormData($requestData);
        $statusCode = $requestData['status'];
        $isNew = $requestData['is_new'];
        $modelData = $requestData;
        unset($modelData['is_new']);
        if (!$isNew) {
            unset($modelData['status']);
        }

        /** @var Status $status */
        $status = $this->_objectManager->create(Status::class);
        $status->load($statusCode);
        // check if status exist
        if ($isNew && $status->getStatus()) {
            $this->messageManager->addErrorMessage(
                __('We found another order status with the same order status code.')
            );

            return $this->redirect('sales/*/new');
        }

        try {
            $status->setData($modelData)->setStatus($statusCode);
            $status->save();
            $this->messageManager->addSuccessMessage(__('You saved the order status.'));

            return $this->redirect('sales/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                $e,
                __('We can\'t add the order status right now.')
            );
        }
        if ($isNew) {
            return $this->redirect('sales/*/new');
        } else {
            return $this->redirect('sales/*/edit', ['status' => $this->getRequest()->getParam('status')]);
        }
    }

    /**
     * @return array|null
     */
    private function gatherRequestData(): ?array
    {
        $data = $this->getRequest()->getPostValue();
        $isNew = $data['is_new'] = $this->getRequest()->getParam('is_new');
        if ($data) {
            $data['status'] = $this->getRequest()->getParam('status');
            //filter tags in labels/status
            /** @var $filterManager FilterManager */
            $filterManager = $this->_objectManager->get(FilterManager::class);
            if ($isNew) {
                $data['status'] = $filterManager->stripTags($data['status']);
            }
            $data['label'] = $filterManager->stripTags($data['label']);
            if (!isset($data['store_labels'])) {
                $data['store_labels'] = [];
            }
            foreach ($data['store_labels'] as &$label) {
                $label = $filterManager->stripTags($label);
            }

            return $data;
        }

        return null;
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return Redirect
     */
    private function redirect(string $path, array $params = []): Redirect
    {
        return $this->resultRedirectFactory->create()->setPath($path, $params);
    }
}
