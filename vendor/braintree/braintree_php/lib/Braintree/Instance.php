<?php
/**
 * Braintree Class Instance template
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 * @abstract
 */
abstract class Braintree_Instance
{
    /**
     *
     * @param array $aAttribs
     */
    public function  __construct($attributes)
    {
        if (!empty($attributes)) {
            $this->_initializeFromArray($attributes);
        }
    }


    /**
     * returns private/nonexistent instance properties
     * @access public
     * @param var $name property name
     * @return mixed contents of instance properties
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } else {
            trigger_error('Undefined property on ' . get_class($this) . ': ' . $name, E_USER_NOTICE);
            return null;
        }
    }

    /**
     * used by isset() and empty()
     * @access public
     * @param string $name property name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return var
     */
    public function  __toString()
    {
        $objOutput = Braintree_Util::implodeAssociativeArray($this->_attributes);
        return get_class($this) .'['.$objOutput.']';
    }
    /**
     * initializes instance properties from the keys/values of an array
     * @ignore
     * @access protected
     * @param <type> $aAttribs array of properties to set - single level
     * @return none
     */
    private function _initializeFromArray($attributes)
    {
        $this->_attributes = $attributes;
    }

}
