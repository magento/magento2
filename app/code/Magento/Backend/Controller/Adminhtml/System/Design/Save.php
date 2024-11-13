<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Filter\FilterInput;

/**
 * Save design action.
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Design implements HttpPostActionInterface
{
    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array|null
     */
    protected function _filterPostData($data)
    {
        $inputFilter = new FilterInput(
            ['date_from' => $this->dateFilter, 'date_to' => $this->dateFilter],
            [],
            $data
        );

        return $inputFilter->getUnescaped();
    }

    /**
     * Save design action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $data['design'] = $this->_filterPostData($data['design']);
            $id = (int)$this->getRequest()->getParam('id');

            $design = $this->_objectManager->create(\Magento\Framework\App\DesignInterface::class);
            if ($id) {
                $design->load($id);
            }

            $design->setData($data['design']);
            if ($id) {
                $design->setId($id);
            }
            try {
                $design->save();
                $this->_eventManager->dispatch('theme_save_after');
                $this->messageManager->addSuccessMessage(__('You saved the design change.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setDesignData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $design->getId()]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
