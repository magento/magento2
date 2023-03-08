<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Tax;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Controller\Adminhtml\Tax;

class AjaxDelete extends Tax
{
    /**
     * Delete Tax Class via AJAX
     *
     * @return ResultJson
     * @throws InvalidArgumentException
     */
    public function execute()
    {
        $classId = (int)$this->getRequest()->getParam('class_id');
        try {
            $this->taxClassRepository->deleteById($classId);
            $responseContent = ['success' => true, 'error_message' => ''];
        } catch (LocalizedException $e) {
            $responseContent = ['success' => false, 'error_message' => $e->getMessage()];
        } catch (Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('We can\'t delete this tax class right now.')
            ];
        }

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
