<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Block\Page\System\Config\Robots;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * "Reset to Defaults" button renderer
 *
 * @deprecated 100.1.6
 * @see Nothing
 */
class Reset extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Page robots default instructions
     */
    public const XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS =
        'design/search_engine_robots/default_custom_instructions';

    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Config::page/system/config/robots/reset.phtml');
    }

    /**
     * Get robots.txt custom instruction default value
     *
     * @return string
     */
    public function getRobotsDefaultCustomInstructions()
    {
        return trim((string)$this->_scopeConfig->getValue(
            self::XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ));
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'reset_to_default_button',
                'label' => __('Reset to Default'),
                'onclick' => 'javascript:resetRobotsToDefault(); return false;',
            ]
        );

        return $button->toHtml();
    }

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
