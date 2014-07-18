<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

            $taxClass = $this->taxClassBuilder
                ->setClassType((string)$this->getRequest()->getPost('class_type'))
                ->setClassName($this->_processClassName((string)$this->getRequest()->getPost('class_name')))
                ->create();
            if ($taxClassId) {
                $this->taxClassService->updateTaxClass($taxClassId, $taxClass);
            } else {
                $taxClassId = $this->taxClassService->createTaxClass($taxClass);
            }

            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                array(
                    'success' => true,
                    'error_message' => '',
                    'class_id' => $taxClassId,
                    'class_name' => $taxClass->getClassName()
                )
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
                array('success' => false, 'error_message' => $e->getMessage(), 'class_id' => '', 'class_name' => '')
            );
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
                array(
                    'success' => false,
                    'error_message' => __('Something went wrong saving this tax class.'),
                    'class_id' => '',
                    'class_name' => ''
                )
            );
        }
        $this->getResponse()->representJson($responseContent);
    }
}
