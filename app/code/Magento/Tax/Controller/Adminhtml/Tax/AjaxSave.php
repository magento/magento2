<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Tax;

class AjaxSave extends \Magento\Tax\Controller\Adminhtml\Tax
{
    /**
     * Save Tax Class via AJAX
     *
     * @return void
     */
    public function execute()
    {
        try {
            $taxClassId = (int)$this->getRequest()->getPost('class_id') ?: null;

            $taxClass = $this->taxClassDataBuilder
                ->setClassId($taxClassId)
                ->setClassType((string)$this->getRequest()->getPost('class_type'))
                ->setClassName($this->_processClassName((string)$this->getRequest()->getPost('class_name')))
                ->create();
            $taxClassId = $this->taxClassRepository->save($taxClass);

            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                [
                    'success' => true,
                    'error_message' => '',
                    'class_id' => $taxClassId,
                    'class_name' => $taxClass->getClassName(),
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
                ['success' => false, 'error_message' => $e->getMessage(), 'class_id' => '', 'class_name' => '']
            );
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
                [
                    'success' => false,
                    'error_message' => __('Something went wrong saving this tax class.'),
                    'class_id' => '',
                    'class_name' => '',
                ]
            );
        }
        $this->getResponse()->representJson($responseContent);
    }
}
