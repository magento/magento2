<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Sales\Order\Address\Form\Renderer;

use Magento\Framework\View\Element\Template;

/**
 * VAT ID element renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Vat extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * Validate button block
     *
     * @var null|\Magento\Backend\Block\Widget\Button
     */
    protected $_validateButton = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Customer::sales/order/create/address/form/renderer/vat.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve validate button block
     *
     * @return \Magento\Backend\Block\Widget\Button
     */
    public function getValidateButton()
    {
        if ($this->_validateButton === null) {
            /** @var $form \Magento\Framework\Data\Form */
            $form = $this->_element->getForm();

            $vatElementId = $this->_element->getHtmlId();

            $countryElementId = $form->getElement('country_id')->getHtmlId();
            $validateUrl = $this->_urlBuilder->getUrl('customer/system_config_validatevat/validateAdvanced');

            $groupMessage = __(
                'The customer is now assigned to Customer Group %s.'
            ) . ' ' . __(
                'Would you like to change the Customer Group for this order?'
            );

            $vatValidateOptions = $this->_jsonEncoder->encode(
                [
                    'vatElementId' => $vatElementId,
                    'countryElementId' => $countryElementId,
                    'groupIdHtmlId' => 'group_id',
                    'validateUrl' => $validateUrl,
                    'vatValidMessage' => __('The VAT ID is valid.'),
                    'vatInvalidMessage' => __('The VAT ID entered (%s) is not a valid VAT ID.'),
                    'vatValidAndGroupValidMessage' => __(
                        'The VAT ID is valid. The current Customer Group will be used.'
                    ),
                    'vatValidAndGroupInvalidMessage' => __(
                        'The VAT ID is valid but no Customer Group is assigned for it.'
                    ),
                    'vatValidAndGroupChangeMessage' => __(
                        'Based on the VAT ID, the customer belongs to the Customer Group %s.'
                    ) . "\n" . $groupMessage,
                    'vatValidationFailedMessage' => __(
                        'Something went wrong while validating the VAT ID.'
                    ),
                    'vatCustomerGroupMessage' => __(
                        'The customer would belong to Customer Group %s.'
                    ),
                    'vatGroupErrorMessage' => __('There was an error detecting Customer Group.'),
                ]
            );

            $optionsVarName = $this->getJsVariablePrefix() . 'VatParameters';
            $beforeHtml = '<script>var ' .
                $optionsVarName .
                ' = ' .
                $vatValidateOptions .
                ';</script>';
            $this->_validateButton = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Validate VAT Number'),
                    'before_html' => $beforeHtml,
                    'onclick' => 'order.validateVat(' . $optionsVarName . ')',
                ]
            );
        }
        return $this->_validateButton;
    }
}
