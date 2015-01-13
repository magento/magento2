<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

/**
 * Form element renderer to display font picker element for VDE
 *
 * @method array getOptions()
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\FontPicker setOptions(array $options)
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\FontPicker setCssClass($class)
 */
class FontPicker extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'font-picker';

    /**
     * Default options which can be limited further by element's 'options' data
     *
     * @var string[]
     */
    protected $_defaultOptions = [
        'Arial, Helvetica, sans-serif',
        'Verdana, Geneva, sans-serif',
        'Tahoma, Geneva, sans-serif',
        'Georgia, serif',
    ];

    /**
     * Constructor helper
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        /*
        $options = array_intersect(array_combine($this->_defaultOptions, $this->_defaultOptions), $this->getOptions());
        $this->setOptions($options);
        */
        $this->setCssClass('element-' . self::CONTROL_TYPE);
    }
}
