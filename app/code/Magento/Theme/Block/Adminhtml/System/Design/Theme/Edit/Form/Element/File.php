<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form element renderer to display file element
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

/**
 * Class \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File
 *
 * @since 2.0.0
 */
class File extends \Magento\Framework\Data\Form\Element\File
{
    /**
     * Additional html attributes
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_htmlAttributes = ['accept', 'multiple'];

    /**
     * Html attributes
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getHtmlAttributes()
    {
        $attributes = parent::getHtmlAttributes();
        return array_merge($attributes, $this->_htmlAttributes);
    }
}
