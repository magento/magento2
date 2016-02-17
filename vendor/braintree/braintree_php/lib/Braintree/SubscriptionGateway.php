<?php
/**
 * Braintree SubscriptionGateway module
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Subscriptions, see {@link http://www.braintreepayments.com/gateway/subscription-api http://www.braintreepaymentsolutions.com/gateway/subscription-api}
 *
 * PHP Version 5
 *
 * @package   Braintree
 * @copyright 2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_SubscriptionGateway
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

    public function create($attributes)
    {
        Braintree_Util::verifyKeys(self::_createSignature(), $attributes);
        $path = $this->_config->merchantPath() . '/subscriptions';
        $response = $this->_http->post($path, array('subscription' => $attributes));
        return $this->_verifyGatewayResponse($response);
    }

    public function find($id)
    {
        $this->_validateId($id);

        try {
            $path = $this->_config->merchantPath() . '/subscriptions/' . $id;
            $response = $this->_http->get($path);
            return Braintree_Subscription::factory($response['subscription']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound('subscription with id ' . $id . ' not found');
        }

    }

    public function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }


        $path = $this->_config->merchantPath() . '/subscriptions/advanced_search_ids';
        $response = $this->_http->post($path, array('search' => $criteria));
        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array($query)
            );

        return new Braintree_ResourceCollection($response, $pager);
    }

    public function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = Braintree_SubscriptionSearch::ids()->in($ids)->toparam();
        $path = $this->_config->merchantPath() . '/subscriptions/advanced_search';
        $response = $this->_http->post($path, array('search' => $criteria));

        return Braintree_Util::extractAttributeAsArray(
            $response['subscriptions'],
            'subscription'
        );
    }

    public function update($subscriptionId, $attributes)
    {
        Braintree_Util::verifyKeys(self::_updateSignature(), $attributes);
        $path = $this->_config->merchantPath() . '/subscriptions/' . $subscriptionId;
        $response = $this->_http->put($path, array('subscription' => $attributes));
        return $this->_verifyGatewayResponse($response);
    }

    public function retryCharge($subscriptionId, $amount = null)
    {
        $transaction_params = array('type' => Braintree_Transaction::SALE,
            'subscriptionId' => $subscriptionId);
        if (isset($amount)) {
            $transaction_params['amount'] = $amount;
        }

        $path = $this->_config->merchantPath() . '/transactions';
        $response = $this->_http->post($path, array('transaction' => $transaction_params));
        return $this->_verifyGatewayResponse($response);
    }

    public function cancel($subscriptionId)
    {
        $path = $this->_config->merchantPath() . '/subscriptions/' . $subscriptionId . '/cancel';
        $response = $this->_http->put($path);
        return $this->_verifyGatewayResponse($response);
    }

    private static function _createSignature()
    {
        return array_merge(
            array(
                'billingDayOfMonth',
                'firstBillingDate',
                'createdAt',
                'updatedAt',
                'id',
                'merchantAccountId',
                'neverExpires',
                'numberOfBillingCycles',
                'paymentMethodToken',
                'paymentMethodNonce',
                'planId',
                'price',
                'trialDuration',
                'trialDurationUnit',
                'trialPeriod',
                array('descriptor' => array('name', 'phone', 'url')),
                array('options' => array('doNotInheritAddOnsOrDiscounts', 'startImmediately')),
            ),
            self::_addOnDiscountSignature()
        );
    }

    private static function _updateSignature()
    {
        return array_merge(
            array(
                'merchantAccountId', 'numberOfBillingCycles', 'paymentMethodToken', 'planId',
                'paymentMethodNonce', 'id', 'neverExpires', 'price',
                array('descriptor' => array('name', 'phone', 'url')),
                array('options' => array('prorateCharges', 'replaceAllAddOnsAndDiscounts', 'revertSubscriptionOnProrationFailure')),
            ),
            self::_addOnDiscountSignature()
        );
    }

    private static function _addOnDiscountSignature()
    {
        return array(
            array(
                'addOns' => array(
                    array('add' => array('amount', 'inheritedFromId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('update' => array('amount', 'existingId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('remove' => array('_anyKey_')),
                )
            ),
            array(
                'discounts' => array(
                    array('add' => array('amount', 'inheritedFromId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('update' => array('amount', 'existingId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('remove' => array('_anyKey_')),
                )
            )
        );
    }

    /**
     * @ignore
     */
    private function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected subscription id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid subscription id.'
                    );
        }
    }

    /**
     * @ignore
     */
    private function _verifyGatewayResponse($response)
    {
        if (isset($response['subscription'])) {
            return new Braintree_Result_Successful(
                Braintree_Subscription::factory($response['subscription'])
            );
        } else if (isset($response['transaction'])) {
            // return a populated instance of Braintree_Transaction, for subscription retryCharge
            return new Braintree_Result_Successful(
                Braintree_Transaction::factory($response['transaction'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            "Expected subscription, transaction, or apiErrorResponse"
            );
        }
    }
}
