<?php
class Braintree_CustomerSearch
{
    static function addressCountryName()               { return new Braintree_TextNode('address_country_name'); }
    static function addressExtendedAddress()           { return new Braintree_TextNode('address_extended_address'); }
    static function addressFirstName()                 { return new Braintree_TextNode('address_first_name'); }
    static function addressLastName()                  { return new Braintree_TextNode('address_last_name'); }
    static function addressLocality()                  { return new Braintree_TextNode('address_locality'); }
    static function addressPostalCode()                { return new Braintree_TextNode('address_postal_code'); }
    static function addressRegion()                    { return new Braintree_TextNode('address_region'); }
    static function addressStreetAddress()             { return new Braintree_TextNode('address_street_address'); }
    static function cardholderName()                   { return new Braintree_TextNode('cardholder_name'); }
    static function company()                          { return new Braintree_TextNode('company'); }
    static function email()                            { return new Braintree_TextNode('email'); }
    static function fax()                              { return new Braintree_TextNode('fax'); }
    static function firstName()                        { return new Braintree_TextNode('first_name'); }
    static function id()                               { return new Braintree_TextNode('id'); }
    static function lastName()                         { return new Braintree_TextNode('last_name'); }
    static function paymentMethodToken()               { return new Braintree_TextNode('payment_method_token'); }
    static function paymentMethodTokenWithDuplicates() { return new Braintree_IsNode('payment_method_token_with_duplicates'); }
    static function paypalAccountEmail()               { return new Braintree_IsNode('paypal_account_email'); }
    static function phone()                            { return new Braintree_TextNode('phone'); }
    static function website()                          { return new Braintree_TextNode('website'); }

    static function creditCardExpirationDate()         { return new Braintree_EqualityNode('credit_card_expiration_date'); }
    static function creditCardNumber()                 { return new Braintree_PartialMatchNode('credit_card_number'); }

    static function ids()                              { return new Braintree_MultipleValueNode('ids'); }

    static function createdAt()                        { return new Braintree_RangeNode("created_at"); }
}
