<?php

class Braintree_ClientToken
{
    const DEFAULT_VERSION = 2;


    // static methods redirecting to gateway

    public static function generate($params=array())
    {
        return Braintree_Configuration::gateway()->clientToken()->generate($params);
    }

    public static function conditionallyVerifyKeys($params)
    {
        return Braintree_Configuration::gateway()->clientToken()->conditionallyVerifyKeys($params);
    }

    public static function generateWithCustomerIdSignature()
    {
        return Braintree_Configuration::gateway()->clientToken()->generateWithCustomerIdSignature();
    }

    public static function generateWithoutCustomerIdSignature()
    {
        return Braintree_Configuration::gateway()->clientToken()->generateWithoutCustomerIdSignature();
    }
}
