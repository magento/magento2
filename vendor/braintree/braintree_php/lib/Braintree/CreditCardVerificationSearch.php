<?php
class Braintree_CreditCardVerificationSearch
{
    static function id()                               { return new Braintree_TextNode('id'); }
    static function creditCardCardholderName()                   { return new Braintree_TextNode('credit_card_cardholder_name'); }

    static function creditCardExpirationDate()         { return new Braintree_EqualityNode('credit_card_expiration_date'); }
    static function creditCardNumber()                 { return new Braintree_PartialMatchNode('credit_card_number'); }

    static function ids()                              { return new Braintree_MultipleValueNode('ids'); }

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


    static function createdAt()                        { return new Braintree_RangeNode("created_at"); }
}
