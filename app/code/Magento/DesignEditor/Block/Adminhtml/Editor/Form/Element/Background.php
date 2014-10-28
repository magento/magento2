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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('name' => $colorHtmlId, 'value' => $colorData['value'], 'title' => $colorTitle, 'label' => null)
        );

        $uploaderId = $this->getComponentId('background-uploader');
        $this->addField(
            $uploaderId,
            'background-uploader',
            array('components' => $uploaderData['components'], 'name' => $uploaderId, 'label' => null)
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
