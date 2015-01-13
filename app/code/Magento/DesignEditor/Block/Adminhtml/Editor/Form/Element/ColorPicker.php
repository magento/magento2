<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

/**
 * Form element renderer to display color picker element for VDE
 *
 * @method string getValue()
 * @method string getExtType()
 * @method string getCssClass()
 * @method string getRequired()
 * @method string getNote()
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ColorPicker setCssClass($class)
 */
class ColorPicker extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'color-picker';

    /**
     * Constructor helper
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setCssClass('element-' . self::CONTROL_TYPE);
    }
}
