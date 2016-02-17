<?php
class Braintree_Discount extends Braintree_Modification
{
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }


    // static methods redirecting to gateway

    public static function all()
    {
        return Braintree_Configuration::gateway()->discount()->all();
    }
}
