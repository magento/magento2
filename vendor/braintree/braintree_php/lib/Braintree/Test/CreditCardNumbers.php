<?php

/**
 * Credit card information used for testing purposes
 *
 * The constants contained in the Braintree_Test_CreditCardNumbers class provide
 * credit card numbers that should be used when working in the sandbox environment.
 * The sandbox will not accept any credit card numbers other than the ones listed below.
 *
 * @package    Braintree
 * @subpackage Test
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Test_CreditCardNumbers
{
    public static $amExes = array(
        '378282246310005',
        '371449635398431',
        '378734493671000',
        );
    public static $carteBlanches = array('30569309025904',);
    public static $dinersClubs   = array('38520000023237',);
    public static $discoverCards = array(
        '6011111111111117',
        '6011000990139424',
        );
    public static $JCBs          = array(
        '3530111333300000',
        '3566002020360505',
        );

    public static $masterCard    = '5555555555554444';
    public static $masterCardInternational = '5105105105105100';
    public static $masterCards   = array(
        '5105105105105100',
        '5555555555554444',
        );

    public static $visa          = '4012888888881881';
    public static $visaInternational = '4009348888881881';
    public static $visas         = array(
        '4009348888881881',
        '4012888888881881',
        '4111111111111111',
        '4000111111111115',
        );

    public static $unknowns       = array(
        '1000000000000008',
        );

    public static $failsSandboxVerification = array(
        'AmEx'       => '378734493671000',
        'Discover'   => '6011000990139424',
        'MasterCard' => '5105105105105100',
        'Visa'       => '4000111111111115',
        );


    public static function getAll()
    {
        return array_merge(
                self::$amExes,
                self::$discoverCards,
                self::$masterCards,
                self::$visas
                );
    }
}
