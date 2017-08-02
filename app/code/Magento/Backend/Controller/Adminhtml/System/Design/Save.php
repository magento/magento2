<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

/**
 * Class \Magento\Backend\Controller\Adminhtml\System\Design\Save
 *
 * @since 2.0.0
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array|null
     * @since 2.0.0
     */
    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['date_from' => $this->dateFilter, 'date_to' => $this->dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
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
                $this->messageManager->addSuccess(__('You saved the design change.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setDesignData($data);
                return $resultRedirect->setPath('adminhtml/*/', ['id' => $design->getId()]);
            }
        }

        return $resultRedirect->setPath('adminhtml/*/');
    }
}
