<?php

final class Braintree_MerchantAccount extends Braintree
{
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';

    const FUNDING_DESTINATION_BANK = 'bank';
    const FUNDING_DESTINATION_EMAIL = 'email';
    const FUNDING_DESTINATION_MOBILE_PHONE = 'mobile_phone';

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    protected function _initialize($merchantAccountAttribs)
    {
        $this->_attributes = $merchantAccountAttribs;

        if (isset($merchantAccountAttribs['individual'])) {
            $individual = $merchantAccountAttribs['individual'];
            $this->_set('individualDetails', Braintree_MerchantAccount_IndividualDetails::Factory($individual));
        }

        if (isset($merchantAccountAttribs['business'])) {
            $business = $merchantAccountAttribs['business'];
            $this->_set('businessDetails', Braintree_MerchantAccount_BusinessDetails::Factory($business));
        }

        if (isset($merchantAccountAttribs['funding'])) {
            $funding = $merchantAccountAttribs['funding'];
            $this->_set('fundingDetails', new Braintree_MerchantAccount_FundingDetails($funding));
        }

        if (isset($merchantAccountAttribs['masterMerchantAccount'])) {
            $masterMerchantAccount = $merchantAccountAttribs['masterMerchantAccount'];
            $this->_set('masterMerchantAccount', Braintree_MerchantAccount::Factory($masterMerchantAccount));
        }
    }


    // static methods redirecting to gateway

    public static function create($attribs)
    {
        return Braintree_Configuration::gateway()->merchantAccount()->create($attribs);
    }

    public static function find($merchant_account_id)
    {
        return Braintree_Configuration::gateway()->merchantAccount()->find($merchant_account_id);
    }

    public static function update($merchant_account_id, $attributes)
    {
        return Braintree_Configuration::gateway()->merchantAccount()->update($merchant_account_id, $attributes);
    }
}
