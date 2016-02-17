<?php

final class Braintree_MerchantAccount_IndividualDetails extends Braintree
{
    protected function _initialize($individualAttribs)
    {
        $this->_attributes = $individualAttribs;
        if (isset($individualAttribs['address'])) {
            $this->_set('addressDetails', new Braintree_MerchantAccount_AddressDetails($individualAttribs['address']));
        }
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
