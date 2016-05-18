<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Block\Adminhtml\System\Config;

/**
 * Frontend model for static compilation mode switcher
 */
class WorkflowType extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_PRODUCTION) {
            $element->setReadonly(true, true);
            $element->setCanUseWebsiteValue(false);
            $element->setCanUseDefaultValue(false);
            $element->setCanRestoreToDefault(false);
        }
        return parent::render($element);
    }
}