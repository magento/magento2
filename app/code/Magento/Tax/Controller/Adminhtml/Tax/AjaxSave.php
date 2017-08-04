<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Tax;

use Magento\Framework\Controller\ResultFactory;

class AjaxSave extends \Magento\Tax\Controller\Adminhtml\Tax
{
    /**
     * Save Tax Class via AJAX
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        try {
            $taxClassId = (int)$this->getRequest()->getPost('class_id') ?: null;

            $taxClass = $this->taxClassDataObjectFactory->create()
                ->setClassId($taxClassId)
                ->setClassType((string)$this->getRequest()->getPost('class_type'))
                ->setClassName($this->_processClassName((string)$this->getRequest()->getPost('class_name')));
            $taxClassId = $this->taxClassRepository->save($taxClass);

            $responseContent = [
                'success' => true,
                'error_message' => '',
                'class_id' => $taxClassId,
                'class_name' => $taxClass->getClassName(),
            ];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage(),
                'class_id' => '',
                'class_name' => ''
            ];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('We can\'t save this tax class right now.'),
                'class_id' => '',
                'class_name' => '',
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
