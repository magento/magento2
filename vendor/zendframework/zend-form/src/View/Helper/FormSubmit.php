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
use Zend\Form\Exception;

class FormSubmit extends FormInput
{
    /**
     * Attributes valid for the input tag type="submit"
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'name'           => true,
        'autofocus'      => true,
        'disabled'       => true,
        'form'           => true,
        'formaction'     => true,
        'formenctype'    => true,
        'formmethod'     => true,
        'formnovalidate' => true,
        'formtarget'     => true,
        'type'           => true,
        'value'          => true,
    );

    /**
     * Translatable attributes
     *
     * @var array
     */
    protected $translatableAttributes = array(
        'value' => true
    );

    /**
     * Determine input type to use
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    protected function getType(ElementInterface $element)
    {
        return 'submit';
    }
}
