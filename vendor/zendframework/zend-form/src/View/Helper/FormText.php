<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use Zend\Form\ElementInterface;

class FormText extends FormInput
{
    /**
     * Attributes valid for the input tag type="text"
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'name'           => true,
        'autocomplete'   => true,
        'autofocus'      => true,
        'dirname'        => true,
        'disabled'       => true,
        'form'           => true,
        'list'           => true,
        'maxlength'      => true,
        'pattern'        => true,
        'placeholder'    => true,
        'readonly'       => true,
        'required'       => true,
        'size'           => true,
        'type'           => true,
        'value'          => true,
    );

    /**
     * Determine input type to use
     *
     * @param  ElementInterface $element
     * @return string
     */
    protected function getType(ElementInterface $element)
    {
        return 'text';
    }
}
