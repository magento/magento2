<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form text element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

/**
 * Class \Magento\Framework\Data\Form\Element\Obscure
 *
 * @since 2.0.0
 */
class Obscure extends \Magento\Framework\Data\Form\Element\Password
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_obscuredValue = '******';

    /**
     * Hide value to make sure it will not show in HTML
     *
     * @param string $index
     * @return string
     * @since 2.0.0
     */
    public function getEscapedValue($index = null)
    {
        $value = parent::getEscapedValue($index);
        if (!empty($value)) {
            return $this->_obscuredValue;
        }
        return $value;
    }

    /**
     * Returns list of html attributes possible to output in HTML
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getHtmlAttributes()
    {
        return [
            'type',
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'onkeyup',
            'disabled',
            'readonly',
            'maxlength',
            'tabindex',
            'data-form-part',
            'data-role',
            'data-action'
        ];
    }
}
