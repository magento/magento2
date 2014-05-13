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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form text element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

class Obscure extends \Magento\Framework\Data\Form\Element\Password
{
    /**
     * @var string
     */
    protected $_obscuredValue = '******';

    /**
     * Hide value to make sure it will not show in HTML
     *
     * @param string $index
     * @return string
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
     */
    public function getHtmlAttributes()
    {
        return array(
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
            'tabindex'
        );
    }
}
