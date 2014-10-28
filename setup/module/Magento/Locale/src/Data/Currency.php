<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Locale\Data;

class Currency implements ListInterface
{
    /**
     * @var array
     */
    protected $data = [
        'AED' => 'United Arab Emirates Dirham',
        'AFN' => 'Afghan Afghani',
        'ALL' => 'Albanian Lek',
        'AMD' => 'Armenian Dram',
        'ANG' => 'Netherlands Antillean Guilder',
        'AOA' => 'Angolan Kwanza',
        'ARS' => 'Argentine Peso',
        'AUD' => 'Australian Dollar',
        'AWG' => 'Aruban Florin',
        'AZM' => 'Azerbaijani Manat (1993-2006)',
        'AZN' => 'Azerbaijani Manat',
        'BAM' => 'Bosnia-Herzegovina Convertible Mark',
        'BBD' => 'Barbadian Dollar',
        'BDT' => 'Bangladeshi Taka',
        'BGN' => 'Bulgarian Lev',
        'BHD' => 'Bahraini Dinar',
        'BIF' => 'Burundian Franc',
        'BMD' => 'Bermudan Dollar',
        'BND' => 'Brunei Dollar',
        'BOB' => 'Bolivian Boliviano',
        'BRL' => 'Brazilian Real',
        'BSD' => 'Bahamian Dollar',
        'BTN' => 'Bhutanese Ngultrum',
        'BUK' => 'Burmese Kyat',
        'BWP' => 'Botswanan Pula',
        'BYR' => 'Belarusian Ruble',
        'BZD' => 'Belize Dollar',
        'CAD' => 'Canadian Dollar',
        'CDF' => 'Congolese Franc',
        'CHE' => 'WIR Euro',
        'CHF' => 'Swiss Franc',
        'CHW' => 'WIR Franc',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Chinese Yuan Renminbi',
        'COP' => 'Colombian Peso',
        'CRC' => 'Costa Rican ColÃ³n',
        'CUP' => 'Cuban Peso',
        'CVE' => 'Cape Verdean Escudo',
        'CZK' => 'Czech Republic Koruna',
        'DJF' => 'Djiboutian Franc',
        'DKK' => 'Danish Krone',
        'DOP' => 'Dominican Peso',
        'DZD' => 'Algerian Dinar',
        'EEK' => 'Estonian Kroon',
        'EGP' => 'Egyptian Pound',
        'ERN' => 'Eritrean Nakfa',
        'ETB' => 'Ethiopian Birr',
        'EUR' => 'Euro',
        'FJD' => 'Fijian Dollar',
        'FKP' => 'Falkland Islands Pound',
        'GBP' => 'British Pound Sterling',
        'GEK' => 'Georgian Kupon Larit',
        'GEL' => 'Georgian Lari',
        'GHS' => 'Ghanaian Cedi',
        'GIP' => 'Gibraltar Pound',
        'GMD' => 'Gambian Dalasi',
        'GNF' => 'Guinean Franc',
        'GQE' => 'Equatorial Guinean Ekwele',
        'GTQ' => 'Guatemalan Quetzal',
        'GYD' => 'Guyanaese Dollar',
        'HKD' => 'Hong Kong Dollar',
        'HNL' => 'Honduran Lempira',
        'HRK' => 'Croatian Kuna',
        'HTG' => 'Haitian Gourde',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli New Sheqel',
        'INR' => 'Indian Rupee',
        'IQD' => 'Iraqi Dinar',
        'IRR' => 'Iranian Rial',
        'ISK' => 'Icelandic KrÃ³na',
        'JMD' => 'Jamaican Dollar',
        'JOD' => 'Jordanian Dinar',
        'JPY' => 'Japanese Yen',
        'KES' => 'Kenyan Shilling',
        'KGS' => 'Kyrgystani Som',
        'KHR' => 'Cambodian Riel',
        'KMF' => 'Comorian Franc',
        'KPW' => 'North Korean Won',
        'KRW' => 'South Korean Won',
        'KWD' => 'Kuwaiti Dinar',
        'KYD' => 'Cayman Islands Dollar',
        'KZT' => 'Kazakhstan Tenge',
        'LAK' => 'Laotian Kip',
        'LBP' => 'Lebanese Pound',
        'LKR' => 'Sri Lanka Rupee',
        'LRD' => 'Liberian Dollar',
        'LSL' => 'Lesotho Loti',
        'LTL' => 'Lithuanian Litas',
        'LVL' => 'Latvian Lats',
        'LYD' => 'Libyan Dinar',
        'MAD' => 'Moroccan Dirham',
        'MDL' => 'Moldovan Leu',
        'MGA' => 'Malagasy Ariary',
        'MKD' => 'Macedonian Denar',
        'MMK' => 'Myanma Kyat',
        'MNT' => 'Mongolian Tugrik',
        'MOP' => 'Macanese Pataca',
        'MRO' => 'Mauritanian Ouguiya',
        'MUR' => 'Mauritian Rupee',
        'MVR' => 'Maldivian Rufiyaa',
        'MWK' => 'Malawian Kwacha',
        'MXN' => 'Mexican Peso',
        'MYR' => 'Malaysian Ringgit',
        'MZN' => 'Mozambican Metical',
        'NAD' => 'Namibian Dollar',
        'NGN' => 'Nigerian Naira',
        'NIC' => 'Nicaraguan Cordoba',
        'NOK' => 'Norwegian Krone',
        'NPR' => 'Nepalese Rupee',
        'NZD' => 'New Zealand Dollar',
        'OMR' => 'Omani Rial',
        'PAB' => 'Panamanian Balboa',
        'PEN' => 'Peruvian Nuevo Sol',
        'PGK' => 'Papua New Guinean Kina',
        'PHP' => 'Philippine Peso',
        'PKR' => 'Pakistani Rupee',
        'PLN' => 'Polish Zloty',
        'PYG' => 'Paraguayan Guarani',
        'QAR' => 'Qatari Rial',
        'RHD' => 'Rhodesian Dollar',
        'ROL' => 'Old Romanian Leu',
        'RON' => 'Romanian Leu',
        'RSD' => 'Serbian Dinar',
        'RUB' => 'Russian Ruble',
        'RWF' => 'Rwandan Franc',
        'SAR' => 'Saudi Riyal',
        'SBD' => 'Solomon Islands Dollar',
        'SCR' => 'Seychellois Rupee',
        'SDG' => 'Sudanese Pound',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'SHP' => 'Saint Helena Pound',
        'SKK' => 'Slovak Koruna',
        'SLL' => 'Sierra Leonean Leone',
        'SOS' => 'Somali Shilling',
        'SRD' => 'Surinamese Dollar',
        'STD' => 'SÃ£o TomÃ© and PrÃ­ncipe Dobra',
        'SVC' => 'Salvadoran ColÃ³n',
        'SYP' => 'Syrian Pound',
        'SZL' => 'Swazi Lilangeni',
        'THB' => 'Thai Baht',
        'TJS' => 'Tajikistani Somoni',
        'TMM' => 'Turkmenistani Manat',
        'TND' => 'Tunisian Dinar',
        'TOP' => 'Tongan PaÊ»anga',
        'TRL' => 'Old Turkish Lira',
        'TRY' => 'Turkish Lira',
        'TTD' => 'Trinidad and Tobago Dollar',
        'TWD' => 'New Taiwan Dollar',
        'TZS' => 'Tanzanian Shilling',
        'UAH' => 'Ukrainian Hryvnia',
        'UGX' => 'Ugandan Shilling',
        'USD' => 'US Dollar',
        'UYU' => 'Uruguayan Peso',
        'UZS' => 'Uzbekistan Som',
        'VEB' => 'Venezuelan BolÃ­var',
        'VEF' => 'Venezuelan BolÃ­var Fuerte',
        'VND' => 'Vietnamese Dong',
        'VUV' => 'Vanuatu Vatu',
        'WST' => 'Samoan Tala',
        'XCD' => 'East Caribbean Dollar',
        'XOF' => 'CFA Franc BCEAO',
        'XPF' => 'CFP Franc',
        'YER' => 'Yemeni Rial',
        'ZAR' => 'South African Rand',
        'ZMK' => 'Zambian Kwacha',
        'ZWD' => 'Zimbabwean Dollar',
    ];

    /**
     * Retrieve list of currencies
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
} 