<?php

class Braintree_HttpClientApi extends Braintree_Http
{

    protected function _doRequest($httpVerb, $path, $requestBody = null)
    {
        return $this->_doUrlRequest($httpVerb, $this->_config->baseUrl() . "/merchants/" . $this->_config->merchantId() . $path, $requestBody);
    }

    public function get($path)
    {
         return $this->_doRequest('GET', $path);
    }

    public function post($path, $body = null)
    {
         return $this->_doRequest('POST', $path, $body);
    }

    public function _doUrlRequest($httpVerb, $url, $requestBody = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-ApiVersion: ' . Braintree_Configuration::API_VERSION
        ));
        curl_setopt($curl, CURLOPT_USERPWD, $this->_config->publicKey() . ':' . $this->_config->privateKey());

        if(!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return array('status' => $httpStatus, 'body' => $response);
    }

    public function get_cards($options) {
        $encoded_fingerprint = urlencode($options["authorization_fingerprint"]);
        $url = "/client_api/v1/payment_methods.json?";
        $url .= "authorizationFingerprint=" . $encoded_fingerprint;
        $url .= "&sharedCustomerIdentifier=" . $options["shared_customer_identifier"];
        $url .= "&sharedCustomerIdentifierType=" . $options["shared_customer_identifier_type"];

        return $this->get($url);
    }

    public function nonce_for_new_card($options) {
        $clientTokenOptions = array();
        if (array_key_exists("customerId", $options)) {
            $clientTokenOptions["customerId"] = $options["customerId"];
            unset($options["customerId"]);
        }
        $clientToken = json_decode(Braintree_TestHelper::decodedClientToken($clientTokenOptions));
        $options["authorization_fingerprint"] = $clientToken->authorizationFingerprint;
        $options["shared_customer_identifier"] = "fake_identifier_" . rand();
        $options["shared_customer_identifier_type"] = "testing";
        $response = $this->post('/client_api/v1/payment_methods/credit_cards.json', json_encode($options));
        if ($response["status"] == 201 || $response["status"] == 202) {
            $body = json_decode($response["body"]);
            return $body->creditCards[0]->nonce;
        } else {
            throw new Exception(var_dump($response));
        }
    }

    public function nonceForPayPalAccount($options) {
        $clientToken = json_decode(Braintree_TestHelper::decodedClientToken());
        $options["authorization_fingerprint"] = $clientToken->authorizationFingerprint;
        $response = $this->post('/client_api/v1/payment_methods/paypal_accounts.json', json_encode($options));
        if ($response["status"] == 201 || $response["status"] == 202) {
            $body = json_decode($response["body"], true);
            return $body["paypalAccounts"][0]["nonce"];
        } else {
            throw new Exception(var_dump($response));
        }
    }
}
