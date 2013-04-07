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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form element renderer to display background uploader element for VDE
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_BackgroundUploader
    extends Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Composite_Abstract
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'background-uploader';

    /**
     * Add form elements
     *
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_BackgroundUploader
     */
    protected function _addFields()
    {
        $uploaderData = $this->getComponent('image-uploader');
        $checkboxData = $this->getComponent('tile');

        $uploaderTitle = sprintf('%s {%s: url(%s)}',
            $uploaderData['selector'],
            $uploaderData['attribute'],
            $uploaderData['value']
        );
        $uploaderId = $this->getComponentId('image-uploader');
        $this->addField($uploaderId, 'image-uploader', array(
            'name'     => $uploaderId,
            'title'    => $uploaderTitle,
            'label'    => null,
            'value'    => $uploaderData['value'] == $uploaderData['default'] ? '' : $uploaderData['value'],
        ));

        $checkboxTitle = sprintf('%s {%s: %s}',
            $checkboxData['selector'],
            $checkboxData['attribute'],
            $checkboxData['value']
        );
        $checkboxHtmlId = $this->getComponentId('tile');
        $this->addField($checkboxHtmlId, 'checkbox', array(
            'name'    => $checkboxHtmlId,
            'title'   => $checkboxTitle,
            'label'   => 'Tile Background',
            'class'   => 'element-checkbox',
            'value'   => 'repeat',
            'checked' => $checkboxData['value'] == 'repeat'
        ))->setUncheckedValue('no-repeat');

        return $this;
    }

    /**
     * Add element types used in composite font element
     *
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_BackgroundUploader
     */
    protected function _addElementTypes()
    {
        $this->addType('image-uploader', 'Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_ImageUploader');

        return $this;
    }

    /**
     * Get component of 'checkbox' type (actually 'tile')
     *
     * @return Varien_Data_Form_Element_Checkbox
     * @throws Mage_Core_Exception
     */
    public function getCheckboxElement()
    {
        $checkboxId = $this->getComponentId('tile');

        /** @var $element Varien_Data_Form_Element_Abstract */
        foreach ($this->getElements() as $element) {
            if ($element->getData('name') == $checkboxId) {
                return $element;
            }
        }

        throw new Mage_Core_Exception(
            $this->_helper->__('Element "%s" is not found in "%s"', $checkboxId, $this->getData('name'))
        );
    }

    /**
     * Get component of 'image-uploader' type
     *
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_ImageUploader
     * @throws Mage_Core_Exception
     */
    public function getImageUploaderElement()
    {
        $imageUploaderId = $this->getComponentId('image-uploader');
        /** @var $e Varien_Data_Form_Element_Abstract */
        foreach ($this->getElements() as $e) {
            if ($e->getData('name') == $imageUploaderId) {
                return $e;
            }
        }
        throw new Mage_Core_Exception(
            $this->_helper->__('Element "%s" is not found in "%s"', $imageUploaderId, $this->getData('name'))
        );
    }
}

