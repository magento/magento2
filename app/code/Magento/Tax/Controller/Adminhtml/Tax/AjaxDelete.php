<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Tax;

use Magento\Framework\Controller\ResultFactory;

class AjaxDelete extends \Magento\Tax\Controller\Adminhtml\Tax
{
    /**
     * Delete Tax Class via AJAX
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $classId = (int)$this->getRequest()->getParam('class_id');
        try {
            $this->taxClassRepository->deleteById($classId);
            $responseContent = ['success' => true, 'error_message' => ''];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = ['success' => false, 'error_message' => $e->getMessage()];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('We can\'t delete this tax class right now.')
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
