<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Report;

/**
 * Class BraintreeSearchNodeStub
 */
class BraintreeTransactionStub
{
    protected $_attributes = [];

    /**
     * Set attributes array
     *
     * @param $attrs
     * @return void
     */
    public function setAttributes($attrs)
    {
        $this->_attributes = $attrs;
    }

    /**
     * Accessor for instance properties stored in the private $_attributes property
     *
     * @ignore
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        trigger_error('Undefined property on ' . get_class($this) . ': ' . $name, E_USER_NOTICE);
        return null;
    }

    /**
     * Checks for the existence of a property stored in the private $_attributes property
     *
     * @ignore
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * Mutator for instance properties stored in the private $_attributes property
     *
     * @ignore
     * @param string $key
     * @param mixed $value
     */
    public function _set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }
}
