<?php

final class Braintree_MerchantAccount_BusinessDetails extends Braintree
{
    protected function _initialize($businessAttribs)
    {
        $this->_attributes = $businessAttribs;
        if (isset($businessAttribs['address'])) {
            $this->_set('addressDetails', new Braintree_MerchantAccount_AddressDetails($businessAttribs['address']));
        }
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
