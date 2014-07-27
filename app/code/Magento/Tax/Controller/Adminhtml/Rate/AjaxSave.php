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
namespace Magento\Tax\Controller\Adminhtml\Rate;

class AjaxSave extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Save Tax Rate via AJAX
     *
     * @return void
     */
    public function execute()
    {
        $responseContent = '';
        try {
            $rateData = $this->_processRateData($this->getRequest()->getPost());
            $taxRate = $this->populateTaxRateData($rateData);
            $taxRateId = $taxRate->getId();
            if ($taxRateId) {
                $this->_taxRateService->updateTaxRate($taxRate);
            } else {
                $taxRate = $this->_taxRateService->createTaxRate($taxRate);
                $taxRateId = $taxRate->getId();
            }
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                array(
                    'success' => true,
                    'error_message' => '',
                    'tax_calculation_rate_id' => $taxRate->getId(),
                    'code' => $taxRate->getCode()
                )
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                array(
                    'success' => false,
                    'error_message' => $e->getMessage(),
                    'tax_calculation_rate_id' => '',
                    'code' => ''
                )
            );
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                array(
                    'success' => false,
                    'error_message' => __('Something went wrong saving this rate.'),
                    'tax_calculation_rate_id' => '',
                    'code' => ''
                )
            );
        }
        $this->getResponse()->representJson($responseContent);
    }
}
