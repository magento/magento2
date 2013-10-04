<?php
/**
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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * VAT ID element renderer
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Customer\Sales\Order\Address\Form\Renderer;

class Vat
    extends \Magento\Adminhtml\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * Validate button block
     *
     * @var null|\Magento\Adminhtml\Block\Widget\Button
     */
    protected $_validateButton = null;

    protected $_template = 'customer/sales/order/create/address/form/renderer/vat.phtml';

    /**
     * Retrieve validate button block
     *
     * @return \Magento\Adminhtml\Block\Widget\Button
     */
    public function getValidateButton()
    {
        if (is_null($this->_validateButton)) {
            /** @var $form \Magento\Data\Form */
            $form = $this->_element->getForm();

            $vatElementId = $this->_element->getHtmlId();

            $countryElementId = $form->getElement('country_id')->getHtmlId();
            $validateUrl = $this->_urlBuilder->getUrl('*/customer_system_config_validatevat/validateAdvanced');

            $groupMessage = __('The customer is currently assigned to Customer Group %s.')
                . ' ' . __('Would you like to change the Customer Group for this order?');

            $vatValidateOptions = $this->_coreData->jsonEncode(array(
                'vatElementId' => $vatElementId,
                'countryElementId' => $countryElementId,
                'groupIdHtmlId' => 'group_id',
                'validateUrl' => $validateUrl,
                'vatValidMessage' => __('The VAT ID is valid. The current Customer Group will be used.'),
                'vatValidAndGroupChangeMessage' => __('Based on the VAT ID, '
                    . 'the customer would belong to the Customer Group %s.')
                    . "\n" . $groupMessage,
                'vatInvalidMessage' => __('The VAT ID entered (%s) is not a valid VAT ID. '
                    . 'The customer would belong to Customer Group %s.')
                    . "\n" . $groupMessage,
                'vatValidationFailedMessage'    => __('There was an error validating the VAT ID. '
                    . 'The customer would belong to Customer Group %s.')
                    . "\n" . $groupMessage,
                'vatErrorMessage' => __('There was an error validating the VAT ID.')
            ));

            $optionsVarName = $this->getJsVariablePrefix() . 'VatParameters';
            $beforeHtml = '<script type="text/javascript">var ' . $optionsVarName . ' = ' . $vatValidateOptions
                . ';</script>';
            $this->_validateButton = $this->getLayout()
                ->createBlock('Magento\Adminhtml\Block\Widget\Button')->setData(array(
                    'label'       => __('Validate VAT Number'),
                    'before_html' => $beforeHtml,
                    'onclick'     => 'order.validateVat(' . $optionsVarName . ')'
            ));
        }
        return $this->_validateButton;
    }
}
