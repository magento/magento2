<?php
class Braintree_Modification extends Braintree
{
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        $addOnArray = array();
        if (isset($attributes['addOns'])) {
            foreach ($attributes['addOns'] AS $addOn) {
                $addOnArray[] = Braintree_addOn::factory($addOn);
            }
        }
        $this->_attributes['addOns'] = $addOnArray;
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
