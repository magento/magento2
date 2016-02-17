<?php
/**
 * Braintree Gateway module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Gateway
{
    public $config;

    public function __construct($config)
    {
        if (is_array($config)) {
            $config = new Braintree_Configuration($config);
        }
        $this->config = $config;
    }

    public function addOn()
    {
        return new Braintree_AddOnGateway($this);
    }

    public function address()
    {
        return new Braintree_AddressGateway($this);
    }

    public function clientToken()
    {
        return new Braintree_ClientTokenGateway($this);
    }

    public function creditCard()
    {
        return new Braintree_CreditCardGateway($this);
    }

    public function creditCardVerification()
    {
        return new Braintree_CreditCardVerificationGateway($this);
    }

    public function customer()
    {
        return new Braintree_CustomerGateway($this);
    }

    public function discount()
    {
        return new Braintree_DiscountGateway($this);
    }

    public function merchant()
    {
        return new Braintree_MerchantGateway($this);
    }

    public function merchantAccount()
    {
        return new Braintree_MerchantAccountGateway($this);
    }

    public function oauth()
    {
        return new Braintree_OAuthGateway($this);
    }

    public function paymentMethod()
    {
        return new Braintree_PaymentMethodGateway($this);
    }

    public function paymentMethodNonce()
    {
        return new Braintree_PaymentMethodNonceGateway($this);
    }

    public function payPalAccount()
    {
        return new Braintree_PayPalAccountGateway($this);
    }

    public function plan()
    {
        return new Braintree_PlanGateway($this);
    }

    public function settlementBatchSummary()
    {
        return new Braintree_SettlementBatchSummaryGateway($this);
    }

    public function subscription()
    {
        return new Braintree_SubscriptionGateway($this);
    }

    public function transaction()
    {
        return new Braintree_TransactionGateway($this);
    }

    public function transparentRedirect()
    {
        return new Braintree_TransparentRedirectGateway($this);
    }
}
