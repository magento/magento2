<?php
class Braintree_AddOn extends Braintree_Modification
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
        return Braintree_Configuration::gateway()->addOn()->all();
    }
}
