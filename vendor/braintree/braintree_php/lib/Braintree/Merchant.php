<?php

final class Braintree_Merchant extends Braintree
{
    protected function _initialize($attribs)
    {
        $this->_attributes = $attribs;
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * returns a string representation of the merchant
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($this->_attributes) .']';
    }
}
