<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form element renderer to display background uploader element for VDE
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

class BackgroundUploader extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Composite\AbstractComposite
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'background-uploader';

    /**
     * Add form elements
     *
     * @return $this
     */
    protected function _addFields()
    {
        $uploaderData = $this->getComponent('image-uploader');
        $checkboxData = $this->getComponent('tile');

        $uploaderTitle = $this->_escape(
            sprintf('%s {%s: url(%s)}', $uploaderData['selector'], $uploaderData['attribute'], $uploaderData['value'])
        );
        $uploaderId = $this->getComponentId('image-uploader');
        $this->addField(
            $uploaderId,
            'image-uploader',
            [
                'name' => $uploaderId,
                'title' => $uploaderTitle,
                'label' => null,
                'value' => trim($uploaderData['value'])
            ]
        );

        $checkboxTitle = $this->_escape(
            sprintf('%s {%s: %s}', $checkboxData['selector'], $checkboxData['attribute'], $checkboxData['value'])
        );
        $checkboxHtmlId = $this->getComponentId('tile');
        $this->addField(
            $checkboxHtmlId,
            'checkbox',
            [
                'name' => $checkboxHtmlId,
                'title' => $checkboxTitle,
                'label' => 'Tile Background',
                'class' => 'element-checkbox',
                'value' => $checkboxData['value'] == 'disabled' ? 'disabled' : 'repeat',
                'checked' => $checkboxData['value'] == 'repeat'
            ]
        )->setUncheckedValue(
            'no-repeat'
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
        $this->addType('image-uploader', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ImageUploader');

        return $this;
    }

    /**
     * Get component of 'checkbox' type (actually 'tile')
     *
     * @return \Magento\Framework\Data\Form\Element\Checkbox
     * @throws \Magento\Framework\Model\Exception
     */
    public function getCheckboxElement()
    {
        $checkboxId = $this->getComponentId('tile');

        /** @var $element \Magento\Framework\Data\Form\Element\AbstractElement */
        foreach ($this->getElements() as $element) {
            if ($element->getData('name') == $checkboxId) {
                return $element;
            }
        }

        throw new \Magento\Framework\Model\Exception(
            __('Element "%1" is not found in "%2".', $checkboxId, $this->getData('name'))
        );
    }

    /**
     * Get component of 'image-uploader' type
     *
     * @return \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ImageUploader
     * @throws \Magento\Framework\Model\Exception
     */
    public function getImageUploaderElement()
    {
        $imageUploaderId = $this->getComponentId('image-uploader');
        /** @var $e \Magento\Framework\Data\Form\Element\AbstractElement */
        foreach ($this->getElements() as $e) {
            if ($e->getData('name') == $imageUploaderId) {
                return $e;
            }
        }
        throw new \Magento\Framework\Model\Exception(
            __('Element "%1" is not found in "%2".', $imageUploaderId, $this->getData('name'))
        );
    }

    /**
     * Return if this element is available to be displayed.
     *
     * @return bool
     */
    public function isTileAvailable()
    {
        return $this->getCheckboxElement()->getData('value') != 'disabled';
    }
}
