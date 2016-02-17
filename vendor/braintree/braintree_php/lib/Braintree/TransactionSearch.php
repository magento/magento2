<?php
class Braintree_TransactionSearch
{
	static function billingCompany()             { return new Braintree_TextNode('billing_company'); }
	static function billingCountryName()         { return new Braintree_TextNode('billing_country_name'); }
	static function billingExtendedAddress()     { return new Braintree_TextNode('billing_extended_address'); }
	static function billingFirstName()           { return new Braintree_TextNode('billing_first_name'); }
	static function billingLastName()            { return new Braintree_TextNode('billing_last_name'); }
	static function billingLocality()            { return new Braintree_TextNode('billing_locality'); }
	static function billingPostalCode()          { return new Braintree_TextNode('billing_postal_code'); }
	static function billingRegion()              { return new Braintree_TextNode('billing_region'); }
	static function billingStreetAddress()       { return new Braintree_TextNode('billing_street_address'); }
	static function creditCardCardholderName()   { return new Braintree_TextNode('credit_card_cardholderName'); }
	static function customerCompany()            { return new Braintree_TextNode('customer_company'); }
	static function customerEmail()              { return new Braintree_TextNode('customer_email'); }
	static function customerFax()                { return new Braintree_TextNode('customer_fax'); }
	static function customerFirstName()          { return new Braintree_TextNode('customer_first_name'); }
	static function customerId()                 { return new Braintree_TextNode('customer_id'); }
	static function customerLastName()           { return new Braintree_TextNode('customer_last_name'); }
	static function customerPhone()              { return new Braintree_TextNode('customer_phone'); }
	static function customerWebsite()            { return new Braintree_TextNode('customer_website'); }
	static function id()                         { return new Braintree_TextNode('id'); }
	static function ids()                        { return new Braintree_MultipleValueNode('ids'); }
	static function orderId()                    { return new Braintree_TextNode('order_id'); }
	static function paymentMethodToken()         { return new Braintree_TextNode('payment_method_token'); }
	static function processorAuthorizationCode() { return new Braintree_TextNode('processor_authorization_code'); }
	static function settlementBatchId()          { return new Braintree_TextNode('settlement_batch_id'); }
	static function shippingCompany()            { return new Braintree_TextNode('shipping_company'); }
	static function shippingCountryName()        { return new Braintree_TextNode('shipping_country_name'); }
	static function shippingExtendedAddress()    { return new Braintree_TextNode('shipping_extended_address'); }
	static function shippingFirstName()          { return new Braintree_TextNode('shipping_first_name'); }
	static function shippingLastName()           { return new Braintree_TextNode('shipping_last_name'); }
	static function shippingLocality()           { return new Braintree_TextNode('shipping_locality'); }
	static function shippingPostalCode()         { return new Braintree_TextNode('shipping_postal_code'); }
	static function shippingRegion()             { return new Braintree_TextNode('shipping_region'); }
	static function shippingStreetAddress()      { return new Braintree_TextNode('shipping_street_address'); }
	static function paypalPaymentId()            { return new Braintree_TextNode('paypal_payment_id'); }
	static function paypalAuthorizationId()      { return new Braintree_TextNode('paypal_authorization_id'); }
	static function paypalPayerEmail()           { return new Braintree_TextNode('paypal_payer_email'); }

	static function creditCardExpirationDate()   { return new Braintree_EqualityNode('credit_card_expiration_date'); }

	static function creditCardNumber()           { return new Braintree_PartialMatchNode('credit_card_number'); }

	static function refund()                     { return new Braintree_KeyValueNode("refund"); }

	static function amount()                     { return new Braintree_RangeNode("amount"); }
	static function authorizedAt()               { return new Braintree_RangeNode("authorizedAt"); }
	static function authorizationExpiredAt()     { return new Braintree_RangeNode("authorizationExpiredAt"); }
	static function createdAt()                  { return new Braintree_RangeNode("createdAt"); }
	static function failedAt()                   { return new Braintree_RangeNode("failedAt"); }
	static function gatewayRejectedAt()          { return new Braintree_RangeNode("gatewayRejectedAt"); }
	static function processorDeclinedAt()        { return new Braintree_RangeNode("processorDeclinedAt"); }
	static function settledAt()                  { return new Braintree_RangeNode("settledAt"); }
	static function submittedForSettlementAt()   { return new Braintree_RangeNode("submittedForSettlementAt"); }
	static function voidedAt()                   { return new Braintree_RangeNode("voidedAt"); }
	static function disbursementDate()           { return new Braintree_RangeNode("disbursementDate"); }
	static function disputeDate()                { return new Braintree_RangeNode("disputeDate"); }

    static function merchantAccountId()          { return new Braintree_MultipleValueNode("merchant_account_id"); }

    static function createdUsing()
    {
        return new Braintree_MultipleValueNode("created_using", array(
            Braintree_Transaction::FULL_INFORMATION,
            Braintree_Transaction::TOKEN
        ));
    }

    static function creditCardCardType()
    {
        return new Braintree_MultipleValueNode("credit_card_card_type", array(
            Braintree_CreditCard::AMEX,
            Braintree_CreditCard::CARTE_BLANCHE,
            Braintree_CreditCard::CHINA_UNION_PAY,
            Braintree_CreditCard::DINERS_CLUB_INTERNATIONAL,
            Braintree_CreditCard::DISCOVER,
            Braintree_CreditCard::JCB,
            Braintree_CreditCard::LASER,
            Braintree_CreditCard::MAESTRO,
            Braintree_CreditCard::MASTER_CARD,
            Braintree_CreditCard::SOLO,
            Braintree_CreditCard::SWITCH_TYPE,
            Braintree_CreditCard::VISA,
            Braintree_CreditCard::UNKNOWN
        ));
    }

    static function creditCardCustomerLocation()
    {
        return new Braintree_MultipleValueNode("credit_card_customer_location", array(
            Braintree_CreditCard::INTERNATIONAL,
            Braintree_CreditCard::US
        ));
    }

    static function source()
    {
        return new Braintree_MultipleValueNode("source", array(
            Braintree_Transaction::API,
            Braintree_Transaction::CONTROL_PANEL,
            Braintree_Transaction::RECURRING,
        ));
    }

    static function status()
    {
        return new Braintree_MultipleValueNode("status", array(
            Braintree_Transaction::AUTHORIZATION_EXPIRED,
            Braintree_Transaction::AUTHORIZING,
            Braintree_Transaction::AUTHORIZED,
            Braintree_Transaction::GATEWAY_REJECTED,
            Braintree_Transaction::FAILED,
            Braintree_Transaction::PROCESSOR_DECLINED,
            Braintree_Transaction::SETTLED,
            Braintree_Transaction::SETTLING,
            Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT,
            Braintree_Transaction::VOIDED,
            Braintree_Transaction::SETTLEMENT_DECLINED,
            Braintree_Transaction::SETTLEMENT_PENDING
        ));
    }

    static function type()
    {
        return new Braintree_MultipleValueNode("type", array(
            Braintree_Transaction::SALE,
            Braintree_Transaction::CREDIT
        ));
    }
}
