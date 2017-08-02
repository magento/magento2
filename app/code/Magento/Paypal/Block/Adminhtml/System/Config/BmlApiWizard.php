<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Adminhtml\System\Config;

/**
 * Class \Magento\Paypal\Block\Adminhtml\System\Config\BmlApiWizard
 *
 * @since 2.0.0
 */
class BmlApiWizard extends ApiWizard
{
    /**
     * Path to block template
     */
    const WIZARD_TEMPLATE = 'system/config/bml_api_wizard.phtml';

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'button_url' => $originalData['button_url'],
                'html_id' => $element->getHtmlId(),
            ]
        );
        return $this->_toHtml();
    }
}
