<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
            $element->addData(
                [
                    'can_use_website_value' => false,
                    'can_use_default_value' => false,
                    'can_restore_to_default' => false
                ]
            );
        }
        return parent::render($element);
    }
}
