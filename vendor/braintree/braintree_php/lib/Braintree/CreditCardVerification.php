<?php
class Braintree_CreditCardVerification extends Braintree_Result_CreditCardVerification
{
    public static function factory($attributes)
    {
        $instance = new self($attributes);
        return $instance;
    }


    // static methods redirecting to gateway

    public static function fetch($query, $ids)
    {
        return Braintree_Configuration::gateway()->creditCardVerification()->fetch($query, $ids);
    }

    public static function search($query)
    {
        return Braintree_Configuration::gateway()->creditCardVerification()->search($query);
    }
}
