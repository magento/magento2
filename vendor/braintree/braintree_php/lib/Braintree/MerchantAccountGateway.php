<?php

final class Braintree_MerchantAccountGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Braintree_Http($gateway->config);
    }

    public function create($attribs)
    {
        Braintree_Util::verifyKeys(self::detectSignature($attribs), $attribs);
        return $this->_doCreate('/merchant_accounts/create_via_api', array('merchant_account' => $attribs));
    }

    public function find($merchant_account_id)
    {
        try {
            $path = $this->_config->merchantPath() . '/merchant_accounts/' . $merchant_account_id;
            $response = $this->_http->get($path);
            return Braintree_MerchantAccount::factory($response['merchantAccount']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound('merchant account with id ' . $merchant_account_id . ' not found');
        }
    }

    public function update($merchant_account_id, $attributes)
    {
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);
        return $this->_doUpdate('/merchant_accounts/' . $merchant_account_id . '/update_via_api', array('merchant_account' => $attributes));
    }

    public static function detectSignature($attribs)
    {
        if (isset($attribs['applicantDetails'])) {
            trigger_error("DEPRECATED: Passing applicantDetails to create is deprecated. Please use individual, business, and funding", E_USER_NOTICE);
            return self::createDeprecatedSignature();
        } else {
            return self::createSignature();
        }
    }

    public static function updateSignature()
    {
        $signature = self::createSignature();
        unset($signature['tosAccepted']);
        return $signature;
    }

    public static function createSignature()
    {
        $addressSignature = array('streetAddress', 'postalCode', 'locality', 'region');
        $individualSignature = array(
            'firstName',
            'lastName',
            'email',
            'phone',
            'dateOfBirth',
            'ssn',
            array('address' => $addressSignature)
        );

        $businessSignature = array(
            'dbaName',
            'legalName',
            'taxId',
            array('address' => $addressSignature)
        );

        $fundingSignature = array(
            'routingNumber',
            'accountNumber',
            'destination',
            'email',
            'mobilePhone',
            'descriptor',
        );

        return array(
            'id',
            'tosAccepted',
            'masterMerchantAccountId',
            array('individual' => $individualSignature),
            array('funding' => $fundingSignature),
            array('business' => $businessSignature)
        );
    }

    public static function createDeprecatedSignature()
    {
        $applicantDetailsAddressSignature = array('streetAddress', 'postalCode', 'locality', 'region');
        $applicantDetailsSignature = array(
            'companyName',
            'firstName',
            'lastName',
            'email',
            'phone',
            'dateOfBirth',
            'ssn',
            'taxId',
            'routingNumber',
            'accountNumber',
            array('address' => $applicantDetailsAddressSignature)
        );

        return array(
            array('applicantDetails' =>  $applicantDetailsSignature),
            'id',
            'tosAccepted',
            'masterMerchantAccountId'
        );
    }

    public function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    private function _doUpdate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->put($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    private function _verifyGatewayResponse($response)
    {
        if (isset($response['merchantAccount'])) {
            // return a populated instance of Braintree_merchantAccount
            return new Braintree_Result_Successful(
                    Braintree_MerchantAccount::factory($response['merchantAccount'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            "Expected merchant account or apiErrorResponse"
            );
        }
    }
}
