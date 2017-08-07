<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config;

/**
 * Custom renderer for PayPal API credentials wizard popup
 */
class ApiWizard extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to block template
     */
    const WIZARD_TEMPLATE = 'system/config/api_wizard.phtml';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::WIZARD_TEMPLATE);
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                // Global
                'query' => $this->createQuery(
                    [
                        'partnerId' => $originalData['partner_id'],
                        'partnerLogoUrl' => $this->_assetRepo->getUrl($originalData['partner_logo_url']),
                        'receiveCredentials' => $originalData['receive_credentials'],
                        'showPermissions' => $originalData['show_permissions'],
                        'displayMode' => $originalData['display_mode'],
                        'productIntentID' => $originalData['product_intent_id'],
                    ]
                ),
                // Live
                'button_label' => __($originalData['button_label']),
                'button_url' => $originalData['button_url'],
                'html_id' => $element->getHtmlId(),
                // Sandbox
                'sandbox_button_label' => __($originalData['sandbox_button_label']),
                'sandbox_button_url' => $originalData['sandbox_button_url'],
                'sandbox_html_id' => 'sandbox_' . $element->getHtmlId(),
            ]
        );
        return $this->_toHtml();
    }

    /**
     * Create request query
     *
     * @param array $requestData
     * @return string
     * @since 2.1.0
     */
    private function createQuery(array $requestData)
    {
        $query = [];

        foreach ($requestData as $name => $value) {
            $query[] = sprintf('%s=%s', $name, $value);
        }

        return implode('&', $query);
    }
}
