<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'data' => [
        'shipper_contact_person_name' => 'testO',
        'shipper_contact_person_first_name' => 'test ',
        'shipper_contact_person_last_name' => 'O',
        'shipper_contact_company_name' => 'testO',
        'shipper_contact_phone_number' => '23424',
        'shipper_email' => 'test@domain.ru',
        'shipper_address_street' => 'mainst1',
        'shipper_address_street1' => 'mainst1',
        'shipper_address_street2' => '',
        'shipper_address_city' => 'Los Angeles',
        'shipper_address_state_or_province_code' => 'CA',
        'shipper_address_postal_code' => '90032',
        'shipper_address_country_code' => 'US',
        'recipient_contact_person_name' => 'DK',
        'recipient_contact_person_first_name' => 'D',
        'recipient_contact_person_last_name' => 'K',
        'recipient_contact_company_name' => 'wsdfsdf',
        'recipient_contact_phone_number' => '234324',
        'recipient_email' => '',
        'recipient_address_street' => '43514 Christy Street',
        'recipient_address_street1' => '43514 Christy Street',
        'recipient_address_street2' => '43514 Christy Street',
        'recipient_address_city' => 'Fremont',
        'recipient_address_state_or_province_code' => 'CA',
        'recipient_address_region_code' => 'CA',
        'recipient_address_postal_code' => '94538',
        'recipient_address_country_code' => 'US',
        'shipping_method' => '6',
        'package_weight' => '5',
        'base_currency_code' => 'USD',
        'store_id' => '1',
        'reference_data' => '#100000001 P',
        'packages' => [
            1 => [
                'params' => [
                    'container' => '',
                    'weight' => 5,
                    'custom_value' => '',
                    'length' => '',
                    'width' => '',
                    'height' => '',
                    'weight_units' => 'POUND',
                    'dimension_units' => 'INCH',
                    'content_type' => '',
                    'content_type_other' => '',
                    'delivery_confirmation' => 'True',
                ],
                'items' => [
                    '2' => [
                        'qty' => '1',
                        'customs_value' => '5',
                        'price' => '5.0000',
                        'name' => 'prod1',
                        'weight' => '5.0000',
                        'product_id' => '1',
                        'order_item_id' => 2,
                    ],
                ],
            ],
        ],
        'order_shipment' => null,
    ]
];
