<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form element renderer to display composite background element for VDE
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

class Background extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Composite\AbstractComposite
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'background';

    /**
     * Add form elements
     *
     * @return $this
     */
    protected function _addFields()
    {
        $colorData = $this->getComponent('color-picker');
        $uploaderData = $this->getComponent('background-uploader');

        $colorTitle = $this->_escape(
            sprintf("%s {%s: %s}", $colorData['selector'], $colorData['attribute'], $colorData['value'])
        );
        $colorHtmlId = $this->getComponentId('color-picker');
        $this->addField(
            $colorHtmlId,
            'color-picker',
            ['name' => $colorHtmlId, 'value' => $colorData['value'], 'title' => $colorTitle, 'label' => null]
        );

        $uploaderId = $this->getComponentId('background-uploader');
        $this->addField(
            $uploaderId,
            'background-uploader',
            ['components' => $uploaderData['components'], 'name' => $uploaderId, 'label' => null]
        );

        return $this;
    }

    /**
     * Add element types used in composite font element
     *
     * @return $this
     */
    protected function _addElementTypes()
    {
        $this->addType('color-picker', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ColorPicker');
        $this->addType(
            'background-uploader',
            'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\BackgroundUploader'
        );

        return $this;
    }
}
