<?php

set_include_path(
  get_include_path() . PATH_SEPARATOR .
  realpath(dirname(__FILE__)) . '/../lib'
);

require_once "Braintree.php";
require_once "Braintree/CreditCardNumbers/CardTypeIndicators.php";
require_once "Braintree/CreditCardDefaults.php";
require_once "Braintree/OAuthTestHelper.php";

function integrationMerchantConfig()
{
    Braintree_Configuration::environment('development');
    Braintree_Configuration::merchantId('integration_merchant_id');
    Braintree_Configuration::publicKey('integration_public_key');
    Braintree_Configuration::privateKey('integration_private_key');
}

function testMerchantConfig()
{
    Braintree_Configuration::environment('development');
    Braintree_Configuration::merchantId('test_merchant_id');
    Braintree_Configuration::publicKey('test_public_key');
    Braintree_Configuration::privateKey('test_private_key');
}

integrationMerchantConfig();

date_default_timezone_set("UTC");

class Braintree_TestHelper
{
    public static function defaultMerchantAccountId()
    {
        return 'sandbox_credit_card';
    }

    public static function nonDefaultMerchantAccountId()
    {
        return 'sandbox_credit_card_non_default';
    }

    public static function nonDefaultSubMerchantAccountId()
    {
        return 'sandbox_sub_merchant_account';
    }

    public static function threeDSecureMerchantAccountId()
    {
        return 'three_d_secure_merchant_account';
    }

    public static function createViaTr($regularParams, $trParams)
    {
        $trData = Braintree_TransparentRedirect::transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return Braintree_TestHelper::submitTrRequest(
            TransparentRedirect::url(),
            $regularParams,
            $trData
        );
    }

    public static function submitTrRequest($url, $regularParams, $trData)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HEADER, true);
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array_merge($regularParams, array('tr_data' => $trData))));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        preg_match('/Location: .*\?(.*)/i', $response, $match);
        return trim($match[1]);
    }

    public static function suppressDeprecationWarnings()
    {
        set_error_handler("Braintree_TestHelper::_errorHandler", E_USER_NOTICE);
    }

    static function _errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (preg_match('/^DEPRECATED/', $errstr) == 0) {
            trigger_error('Unknown error received: ' . $errstr, E_USER_ERROR);
        }
    }

    public static function includes($collection, $targetItem)
    {
        foreach ($collection AS $item) {
            if ($item->id == $targetItem->id) {
                return true;
            }
        }
        return false;
    }

    public static function assertPrintable($object)
    {
        " " . $object;
    }

    public static function settle($transactionId)
    {
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $transactionId . '/settle';
        $http->put($path);
    }

    public static function settlementDecline($transactionId)
    {
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $transactionId . '/settlement_decline';
        $http->put($path);
    }

    public static function settlementPending($transactionId)
    {
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $transactionId . '/settlement_pending';
        $http->put($path);
    }

    public static function escrow($transactionId)
    {
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $transactionId . '/escrow';
        $http->put($path);
    }

    public static function create3DSVerification($merchantAccountId, $params)
    {
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/three_d_secure/create_verification/' . $merchantAccountId;
        $response = $http->post($path, array('threeDSecureVerification' => $params));
        return $response['threeDSecureVerification']['threeDSecureToken'];
    }

    public static function nowInEastern()
    {
        $eastern = new DateTimeZone('America/New_York');
        $now = new DateTime('now', $eastern);
        return $now->format('Y-m-d');
    }

    public static function decodedClientToken($params=array()) {
        $encodedClientToken = Braintree_ClientToken::generate($params);
        return base64_decode($encodedClientToken);
    }
}
