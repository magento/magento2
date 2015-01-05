<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

/**
 * Form element renderer to display file element for VDE
 *
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Uploader setAccept($accept)
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Uploader setMultiple(bool $isMultiple)
 */
class Uploader extends \Magento\Framework\Data\Form\Element\File
{
    //const CONTROL_TYPE = 'uploader';

    /**
     * Additional html attributes
     *
     * @var string[]
     */
    protected $_htmlAttributes = ['accept', 'multiple'];

    /**
     * Html attributes
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        $attributes = parent::getHtmlAttributes();
        return array_merge($attributes, $this->_htmlAttributes);
    }
}
