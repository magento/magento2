<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Form element renderer to display composite logo element for VDE
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

class Logo extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Composite\AbstractComposite
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'logo';

    /**
     * Add form elements
     *
     * @return $this
     */
    protected function _addFields()
    {
        $uploaderData = $this->getComponent('logo-uploader');
        $uploaderTitle = $this->_escape(
            sprintf('%s {%s: url(%s)}', $uploaderData['selector'], $uploaderData['attribute'], $uploaderData['value'])
        );
        $uploaderId = $this->getComponentId('logo-uploader');
        $this->addField(
            $uploaderId,
            'logo-uploader',
            ['name' => $uploaderId, 'title' => $uploaderTitle, 'label' => null]
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
        $this->addType('logo-uploader', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\LogoUploader');
        return $this;
    }
}
