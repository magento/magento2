<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

use Zend\InputFilter\InputFilterInterface;

interface FormInterface extends FieldsetInterface
{
    const BIND_ON_VALIDATE  = 0x00;
    const BIND_MANUAL       = 0x01;
    const VALIDATE_ALL      = 0x10;
    const VALUES_NORMALIZED = 0x11;
    const VALUES_RAW        = 0x12;
    const VALUES_AS_ARRAY   = 0x13;

    /**
     * Set data to validate and/or populate elements
     *
     * Typically, also passes data on to the composed input filter.
     *
     * @param  array|\ArrayAccess $data
     * @return FormInterface
     */
    public function setData($data);

    /**
     * Bind an object to the element
     *
     * Allows populating the object with validated values.
     *
     * @param  object $object
     * @param  int $flags
     * @return mixed
     */
    public function bind($object, $flags = FormInterface::VALUES_NORMALIZED);

    /**
     * Whether or not to bind values to the bound object when validation succeeds
     *
     * @param  int $bindOnValidateFlag
     * @return void
     */
    public function setBindOnValidate($bindOnValidateFlag);

    /**
     * Set input filter
     *
     * @param  InputFilterInterface $inputFilter
     * @return FormInterface
     */
    public function setInputFilter(InputFilterInterface $inputFilter);

    /**
     * Retrieve input filter
     *
     * @return InputFilterInterface
     */
    public function getInputFilter();

    /**
     * Validate the form
     *
     * Typically, will proxy to the composed input filter.
     *
     * @return bool
     */
    public function isValid();

    /**
     * Retrieve the validated data
     *
     * By default, retrieves normalized values; pass one of the VALUES_*
     * constants to shape the behavior.
     *
     * @param  int $flag
     * @return array|object
     */
    public function getData($flag = FormInterface::VALUES_NORMALIZED);

    /**
     * Set the validation group (set of values to validate)
     *
     * Typically, proxies to the composed input filter
     *
     * @return FormInterface
     */
    public function setValidationGroup();
}
