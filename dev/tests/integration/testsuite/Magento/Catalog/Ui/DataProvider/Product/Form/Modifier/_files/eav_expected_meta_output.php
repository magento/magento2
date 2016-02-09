<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    "product-details" => [
        "children" => [
            "status" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => 1,
                        "label" => "Enabled"
                    ],
                    [
                        "value" => 2,
                        "label" => "Disabled"
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Enable Product",
                "default" => "1",
                "dataScope" => "status",
                "source" => "product-details",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "status",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "name" => [
                "dataType" => "text",
                "formElement" => "input",
                "visible" => "1",
                "required" => "1",
                "label" => "Product Name",
                "dataScope" => "name",
                "source" => "product-details",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "name",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field",
                "validation" => [
                    "required-entry" => true
                ]
            ],
            "sku" => [
                "dataType" => "text",
                "formElement" => "input",
                "visible" => "1",
                "required" => "1",
                "label" => "SKU",
                "dataScope" => "sku",
                "source" => "product-details",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "sku",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field",
                "validation" => [
                    "required-entry" => true
                ]
            ],
            "price" => [
                "dataType" => "price",
                "formElement" => "input",
                "visible" => "1",
                "required" => "1",
                "label" => "Price",
                "dataScope" => "price",
                "source" => "product-details",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "price",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field",
                "validation" => [
                    "required-entry" => true
                ]
            ],
            "tax_class_id" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => "0",
                        "label" => "None"
                    ],
                    [
                        "value" => "2",
                        "label" => "Taxable Goods"
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Tax Class",
                "default" => "2",
                "dataScope" => "tax_class_id",
                "source" => "product-details",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "tax_class_id",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "quantity_and_stock_status" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => 1,
                        "label" => "In Stock"
                    ],
                    [
                        "value" => 0,
                        "label" => "Out of Stock"
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Quantity",
                "default" => "1",
                "dataScope" => "quantity_and_stock_status",
                "source" => "product-details",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "quantity_and_stock_status",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "weight" => [
                "dataType" => "weight",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "Weight",
                "dataScope" => "weight",
                "source" => "product-details",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "weight",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "visibility" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => 1,
                        "label" => "Not Visible Individually"
                    ],
                    [
                        "value" => 2,
                        "label" => "Catalog"
                    ],
                    [
                        "value" => 3,
                        "label" => "Search"
                    ],
                    [
                        "value" => 4,
                        "label" => "Catalog, Search"
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Visibility",
                "default" => "4",
                "dataScope" => "visibility",
                "source" => "product-details",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "visibility",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "category_ids" => [
                "dataType" => "text",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "Categories",
                "dataScope" => "category_ids",
                "source" => "product-details",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "category_ids",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "news_from_date" => [
                "dataType" => "date",
                "formElement" => "date",
                "visible" => "1",
                "required" => "0",
                "label" => "Set Product as New from Date",
                "dataScope" => "news_from_date",
                "source" => "product-details",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "news_from_date",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "news_to_date" => [
                "dataType" => "date",
                "formElement" => "date",
                "visible" => "1",
                "required" => "0",
                "label" => "Set Product as New to Date",
                "dataScope" => "news_to_date",
                "source" => "product-details",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "news_to_date",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "country_of_manufacture" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => "",
                        "label" => " "
                    ],
                    [
                        "value" => "AF",
                        "label" => "Afghanistan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AX",
                        "label" => "Åland Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AL",
                        "label" => "Albania",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "DZ",
                        "label" => "Algeria",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AS",
                        "label" => "American Samoa",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AD",
                        "label" => "Andorra",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AO",
                        "label" => "Angola",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AI",
                        "label" => "Anguilla",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AQ",
                        "label" => "Antarctica",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AG",
                        "label" => "Antigua and Barbuda",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AR",
                        "label" => "Argentina",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AM",
                        "label" => "Armenia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AW",
                        "label" => "Aruba",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AU",
                        "label" => "Australia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AT",
                        "label" => "Austria",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "AZ",
                        "label" => "Azerbaijan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BS",
                        "label" => "Bahamas",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BH",
                        "label" => "Bahrain",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BD",
                        "label" => "Bangladesh",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BB",
                        "label" => "Barbados",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BY",
                        "label" => "Belarus",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BE",
                        "label" => "Belgium",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BZ",
                        "label" => "Belize",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BJ",
                        "label" => "Benin",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BM",
                        "label" => "Bermuda",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BT",
                        "label" => "Bhutan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BO",
                        "label" => "Bolivia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BA",
                        "label" => "Bosnia and Herzegovina",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BW",
                        "label" => "Botswana",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BV",
                        "label" => "Bouvet Island",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BR",
                        "label" => "Brazil",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "IO",
                        "label" => "British Indian Ocean Territory",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "VG",
                        "label" => "British Virgin Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BN",
                        "label" => "Brunei",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BG",
                        "label" => "Bulgaria",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BF",
                        "label" => "Burkina Faso",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BI",
                        "label" => "Burundi",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KH",
                        "label" => "Cambodia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CM",
                        "label" => "Cameroon",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CA",
                        "label" => "Canada",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "CV",
                        "label" => "Cape Verde",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KY",
                        "label" => "Cayman Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CF",
                        "label" => "Central African Republic",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TD",
                        "label" => "Chad",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CL",
                        "label" => "Chile",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CN",
                        "label" => "China",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CX",
                        "label" => "Christmas Island",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CC",
                        "label" => "Cocos (Keeling) Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CO",
                        "label" => "Colombia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KM",
                        "label" => "Comoros",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CG",
                        "label" => "Congo - Brazzaville",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CD",
                        "label" => "Congo - Kinshasa",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CK",
                        "label" => "Cook Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CR",
                        "label" => "Costa Rica",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CI",
                        "label" => "Côte d’Ivoire",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "HR",
                        "label" => "Croatia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CU",
                        "label" => "Cuba",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CY",
                        "label" => "Cyprus",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CZ",
                        "label" => "Czech Republic",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "DK",
                        "label" => "Denmark",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "DJ",
                        "label" => "Djibouti",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "DM",
                        "label" => "Dominica",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "DO",
                        "label" => "Dominican Republic",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "EC",
                        "label" => "Ecuador",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "EG",
                        "label" => "Egypt",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SV",
                        "label" => "El Salvador",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GQ",
                        "label" => "Equatorial Guinea",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ER",
                        "label" => "Eritrea",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "EE",
                        "label" => "Estonia",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "ET",
                        "label" => "Ethiopia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "FK",
                        "label" => "Falkland Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "FO",
                        "label" => "Faroe Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "FJ",
                        "label" => "Fiji",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "FI",
                        "label" => "Finland",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "FR",
                        "label" => "France",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GF",
                        "label" => "French Guiana",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PF",
                        "label" => "French Polynesia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TF",
                        "label" => "French Southern Territories",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GA",
                        "label" => "Gabon",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GM",
                        "label" => "Gambia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GE",
                        "label" => "Georgia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "DE",
                        "label" => "Germany",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GH",
                        "label" => "Ghana",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GI",
                        "label" => "Gibraltar",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GR",
                        "label" => "Greece",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GL",
                        "label" => "Greenland",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GD",
                        "label" => "Grenada",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GP",
                        "label" => "Guadeloupe",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GU",
                        "label" => "Guam",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GT",
                        "label" => "Guatemala",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GG",
                        "label" => "Guernsey",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GN",
                        "label" => "Guinea",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GW",
                        "label" => "Guinea-Bissau",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GY",
                        "label" => "Guyana",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "HT",
                        "label" => "Haiti",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "HM",
                        "label" => "Heard & McDonald Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "HN",
                        "label" => "Honduras",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "HK",
                        "label" => "Hong Kong SAR China",
                        "is_region_visible" => true,
                        "is_zipcode_optional" => true
                    ],
                    [
                        "value" => "HU",
                        "label" => "Hungary",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "IS",
                        "label" => "Iceland",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "IN",
                        "label" => "India",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ID",
                        "label" => "Indonesia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "IR",
                        "label" => "Iran",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "IQ",
                        "label" => "Iraq",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "IE",
                        "label" => "Ireland",
                        "is_region_visible" => true,
                        "is_zipcode_optional" => true
                    ],
                    [
                        "value" => "IM",
                        "label" => "Isle of Man",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "IL",
                        "label" => "Israel",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "IT",
                        "label" => "Italy",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "JM",
                        "label" => "Jamaica",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "JP",
                        "label" => "Japan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "JE",
                        "label" => "Jersey",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "JO",
                        "label" => "Jordan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KZ",
                        "label" => "Kazakhstan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KE",
                        "label" => "Kenya",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KI",
                        "label" => "Kiribati",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KW",
                        "label" => "Kuwait",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KG",
                        "label" => "Kyrgyzstan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LA",
                        "label" => "Laos",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LV",
                        "label" => "Latvia",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "LB",
                        "label" => "Lebanon",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LS",
                        "label" => "Lesotho",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LR",
                        "label" => "Liberia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LY",
                        "label" => "Libya",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LI",
                        "label" => "Liechtenstein",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LT",
                        "label" => "Lithuania",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "LU",
                        "label" => "Luxembourg",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MO",
                        "label" => "Macau SAR China",
                        "is_region_visible" => true,
                        "is_zipcode_optional" => true
                    ],
                    [
                        "value" => "MK",
                        "label" => "Macedonia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MG",
                        "label" => "Madagascar",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MW",
                        "label" => "Malawi",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MY",
                        "label" => "Malaysia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MV",
                        "label" => "Maldives",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ML",
                        "label" => "Mali",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MT",
                        "label" => "Malta",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MH",
                        "label" => "Marshall Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MQ",
                        "label" => "Martinique",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MR",
                        "label" => "Mauritania",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MU",
                        "label" => "Mauritius",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "YT",
                        "label" => "Mayotte",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MX",
                        "label" => "Mexico",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "FM",
                        "label" => "Micronesia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MD",
                        "label" => "Moldova",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MC",
                        "label" => "Monaco",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MN",
                        "label" => "Mongolia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ME",
                        "label" => "Montenegro",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MS",
                        "label" => "Montserrat",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MA",
                        "label" => "Morocco",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MZ",
                        "label" => "Mozambique",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MM",
                        "label" => "Myanmar (Burma)",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NA",
                        "label" => "Namibia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NR",
                        "label" => "Nauru",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NP",
                        "label" => "Nepal",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NL",
                        "label" => "Netherlands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AN",
                        "label" => "Netherlands Antilles",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NC",
                        "label" => "New Caledonia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NZ",
                        "label" => "New Zealand",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NI",
                        "label" => "Nicaragua",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NE",
                        "label" => "Niger",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NG",
                        "label" => "Nigeria",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NU",
                        "label" => "Niue",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NF",
                        "label" => "Norfolk Island",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MP",
                        "label" => "Northern Mariana Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KP",
                        "label" => "North Korea",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "NO",
                        "label" => "Norway",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "OM",
                        "label" => "Oman",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PK",
                        "label" => "Pakistan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PW",
                        "label" => "Palau",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PS",
                        "label" => "Palestinian Territories",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PA",
                        "label" => "Panama",
                        "is_region_visible" => true,
                        "is_zipcode_optional" => true
                    ],
                    [
                        "value" => "PG",
                        "label" => "Papua New Guinea",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PY",
                        "label" => "Paraguay",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PE",
                        "label" => "Peru",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PH",
                        "label" => "Philippines",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PN",
                        "label" => "Pitcairn Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PL",
                        "label" => "Poland",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PT",
                        "label" => "Portugal",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "QA",
                        "label" => "Qatar",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "RE",
                        "label" => "Réunion",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "RO",
                        "label" => "Romania",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "RU",
                        "label" => "Russia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "RW",
                        "label" => "Rwanda",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "BL",
                        "label" => "Saint Barthélemy",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SH",
                        "label" => "Saint Helena",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KN",
                        "label" => "Saint Kitts and Nevis",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "LC",
                        "label" => "Saint Lucia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "MF",
                        "label" => "Saint Martin",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "PM",
                        "label" => "Saint Pierre and Miquelon",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "WS",
                        "label" => "Samoa",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SM",
                        "label" => "San Marino",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ST",
                        "label" => "São Tomé and Príncipe",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SA",
                        "label" => "Saudi Arabia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SN",
                        "label" => "Senegal",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "RS",
                        "label" => "Serbia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SC",
                        "label" => "Seychelles",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SL",
                        "label" => "Sierra Leone",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SG",
                        "label" => "Singapore",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SK",
                        "label" => "Slovakia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SI",
                        "label" => "Slovenia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SB",
                        "label" => "Solomon Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SO",
                        "label" => "Somalia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ZA",
                        "label" => "South Africa",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GS",
                        "label" => "South Georgia & South Sandwich Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "KR",
                        "label" => "South Korea",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ES",
                        "label" => "Spain",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "LK",
                        "label" => "Sri Lanka",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "VC",
                        "label" => "St. Vincent & Grenadines",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SD",
                        "label" => "Sudan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SR",
                        "label" => "Suriname",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SJ",
                        "label" => "Svalbard and Jan Mayen",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SZ",
                        "label" => "Swaziland",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "SE",
                        "label" => "Sweden",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "CH",
                        "label" => "Switzerland",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "SY",
                        "label" => "Syria",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TW",
                        "label" => "Taiwan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TJ",
                        "label" => "Tajikistan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TZ",
                        "label" => "Tanzania",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TH",
                        "label" => "Thailand",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TL",
                        "label" => "Timor-Leste",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TG",
                        "label" => "Togo",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TK",
                        "label" => "Tokelau",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TO",
                        "label" => "Tonga",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TT",
                        "label" => "Trinidad and Tobago",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TN",
                        "label" => "Tunisia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TR",
                        "label" => "Turkey",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TM",
                        "label" => "Turkmenistan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TC",
                        "label" => "Turks and Caicos Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "TV",
                        "label" => "Tuvalu",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "UG",
                        "label" => "Uganda",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "UA",
                        "label" => "Ukraine",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "AE",
                        "label" => "United Arab Emirates",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "GB",
                        "label" => "United Kingdom",
                        "is_region_visible" => true,
                        "is_zipcode_optional" => true
                    ],
                    [
                        "value" => "US",
                        "label" => "United States",
                        "is_region_required" => true
                    ],
                    [
                        "value" => "UY",
                        "label" => "Uruguay",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "UM",
                        "label" => "U.S. Outlying Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "VI",
                        "label" => "U.S. Virgin Islands",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "UZ",
                        "label" => "Uzbekistan",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "VU",
                        "label" => "Vanuatu",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "VA",
                        "label" => "Vatican City",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "VE",
                        "label" => "Venezuela",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "VN",
                        "label" => "Vietnam",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "WF",
                        "label" => "Wallis and Futuna",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "EH",
                        "label" => "Western Sahara",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "YE",
                        "label" => "Yemen",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ZM",
                        "label" => "Zambia",
                        "is_region_visible" => true
                    ],
                    [
                        "value" => "ZW",
                        "label" => "Zimbabwe",
                        "is_region_visible" => true
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Country of Manufacture",
                "dataScope" => "country_of_manufacture",
                "source" => "product-details",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "country_of_manufacture",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ]
        ],
        "label" => "Product Details",
        "collapsible" => true,
        "dataScope" => "data.product",
        "sortOrder" => "__placeholder__",
        "componentType" => "fieldset"
    ],
    "content" => [
        "children" => [
            "description" => [
                "dataType" => "textarea",
                "formElement" => "textarea",
                "visible" => "1",
                "required" => "0",
                "label" => "Description",
                "dataScope" => "description",
                "source" => "content",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "description",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "short_description" => [
                "dataType" => "textarea",
                "formElement" => "textarea",
                "visible" => "1",
                "required" => "0",
                "label" => "Short Description",
                "dataScope" => "short_description",
                "source" => "content",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "short_description",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ]
        ],
        "label" => "Content",
        "collapsible" => true,
        "dataScope" => "data.product",
        "sortOrder" => "__placeholder__",
        "componentType" => "fieldset"
    ],
    "image-management" => [
        "children" => [
            "image" => [
                "dataType" => "media_image",
                "formElement" => "image",
                "visible" => "1",
                "required" => "0",
                "label" => "Base",
                "dataScope" => "image",
                "source" => "image-management",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "image",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "small_image" => [
                "dataType" => "media_image",
                "formElement" => "image",
                "visible" => "1",
                "required" => "0",
                "label" => "Small",
                "dataScope" => "small_image",
                "source" => "image-management",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "small_image",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "thumbnail" => [
                "dataType" => "media_image",
                "formElement" => "image",
                "visible" => "1",
                "required" => "0",
                "label" => "Thumbnail",
                "dataScope" => "thumbnail",
                "source" => "image-management",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "thumbnail",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "swatch_image" => [
                "dataType" => "media_image",
                "formElement" => "image",
                "visible" => "1",
                "required" => "0",
                "label" => "Swatch",
                "dataScope" => "swatch_image",
                "source" => "image-management",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "swatch_image",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "media_gallery" => [
                "dataType" => "gallery",
                "formElement" => "image",
                "visible" => "1",
                "required" => "0",
                "label" => "Media Gallery",
                "dataScope" => "media_gallery",
                "source" => "image-management",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "media_gallery",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "gallery" => [
                "dataType" => "gallery",
                "formElement" => "image",
                "visible" => "1",
                "required" => "0",
                "label" => "Image Gallery",
                "dataScope" => "gallery",
                "source" => "image-management",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "gallery",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ]
        ],
        "label" => "Images",
        "collapsible" => true,
        "dataScope" => "data.product",
        "sortOrder" => "__placeholder__",
        "componentType" => "fieldset"
    ],
    "search-engine-optimization" => [
        "children" => [
            "url_key" => [
                "dataType" => "text",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "URL Key",
                "dataScope" => "url_key",
                "source" => "search-engine-optimization",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "url_key",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "meta_title" => [
                "dataType" => "text",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "Meta Title",
                "dataScope" => "meta_title",
                "source" => "search-engine-optimization",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "meta_title",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "meta_keyword" => [
                "dataType" => "textarea",
                "formElement" => "textarea",
                "visible" => "1",
                "required" => "0",
                "label" => "Meta Keywords",
                "dataScope" => "meta_keyword",
                "source" => "search-engine-optimization",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "meta_keyword",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "meta_description" => [
                "dataType" => "textarea",
                "formElement" => "textarea",
                "visible" => "1",
                "required" => "0",
                "label" => "Meta Description",
                "notice" => "Maximum 255 chars",
                "dataScope" => "meta_description",
                "source" => "search-engine-optimization",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "meta_description",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ]
        ],
        "label" => "Search Engine Optimization",
        "collapsible" => true,
        "dataScope" => "data.product",
        "sortOrder" => "__placeholder__",
        "componentType" => "fieldset"
    ],
    "advanced-pricing" => [
        "children" => [
            "special_price" => [
                "dataType" => "price",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "Special Price",
                "dataScope" => "special_price",
                "source" => "advanced-pricing",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "special_price",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "special_from_date" => [
                "dataType" => "date",
                "formElement" => "date",
                "visible" => "1",
                "required" => "0",
                "label" => "Special Price From Date",
                "dataScope" => "special_from_date",
                "source" => "advanced-pricing",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "special_from_date",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "special_to_date" => [
                "dataType" => "date",
                "formElement" => "date",
                "visible" => "1",
                "required" => "0",
                "label" => "Special Price To Date",
                "dataScope" => "special_to_date",
                "source" => "advanced-pricing",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "special_to_date",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "cost" => [
                "dataType" => "price",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "Cost",
                "dataScope" => "cost",
                "source" => "advanced-pricing",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "cost",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "tier_price" => [
                "dataType" => "text",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "Tier Price",
                "dataScope" => "tier_price",
                "source" => "advanced-pricing",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "tier_price",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "msrp" => [
                "dataType" => "price",
                "formElement" => "input",
                "visible" => "1",
                "required" => "0",
                "label" => "Manufacturer's Suggested Retail Price",
                "dataScope" => "msrp",
                "source" => "advanced-pricing",
                "scopeLabel" => "[GLOBAL]",
                "globalScope" => true,
                "code" => "msrp",
                "usedDefault" => false,
                "sortOrder" => "__placeholder__",
                "componentType" => "field"
            ],
            "msrp_display_actual_price_type" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "label" => "Use config",
                        "value" => 0
                    ],
                    [
                        "label" => "On Gesture",
                        "value" => 1
                    ],
                    [
                        "label" => "In Cart",
                        "value" => 2
                    ],
                    [
                        "label" => "Before Order Confirmation",
                        "value" => 3
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Display Actual Price",
                "default" => "0",
                "dataScope" => "msrp_display_actual_price_type",
                "source" => "advanced-pricing",
                "scopeLabel" => "[WEBSITE]",
                "globalScope" => false,
                "code" => "msrp_display_actual_price_type",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ]
        ],
        "label" => "Advanced Pricing",
        "collapsible" => true,
        "dataScope" => "data.product",
        "sortOrder" => "__placeholder__",
        "componentType" => "fieldset"
    ],
    "design" => [
        "children" => [
            "page_layout" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => "",
                        "label" => "No layout updates"
                    ],
                    [
                        "label" => "Empty",
                        "value" => "empty"
                    ],
                    [
                        "label" => "1 column",
                        "value" => "1column"
                    ],
                    [
                        "label" => "2 columns with left bar",
                        "value" => "2columns-left"
                    ],
                    [
                        "label" => "2 columns with right bar",
                        "value" => "2columns-right"
                    ],
                    [
                        "label" => "3 columns",
                        "value" => "3columns"
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Layout",
                "dataScope" => "page_layout",
                "source" => "design",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "page_layout",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "options_container" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => "container1",
                        "label" => "Product Info Column"
                    ],
                    [
                        "value" => "container2",
                        "label" => "Block after Info Column"
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "Display Product Options In",
                "default" => "container2",
                "dataScope" => "options_container",
                "source" => "design",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "options_container",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "custom_layout_update" => [
                "dataType" => "textarea",
                "formElement" => "textarea",
                "visible" => "1",
                "required" => "0",
                "label" => "Layout Update XML",
                "dataScope" => "custom_layout_update",
                "source" => "design",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "custom_layout_update",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ]
        ],
        "label" => "Design",
        "collapsible" => true,
        "dataScope" => "data.product",
        "sortOrder" => "__placeholder__",
        "componentType" => "fieldset"
    ],
    "schedule-design-update" => [
        "children" => [
            "custom_design_from" => [
                "dataType" => "date",
                "formElement" => "date",
                "visible" => "1",
                "required" => "0",
                "label" => "Active From",
                "dataScope" => "custom_design_from",
                "source" => "schedule-design-update",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "custom_design_from",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "custom_design_to" => [
                "dataType" => "date",
                "formElement" => "date",
                "visible" => "1",
                "required" => "0",
                "label" => "Active To",
                "dataScope" => "custom_design_to",
                "source" => "schedule-design-update",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "custom_design_to",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "custom_design" => [
                "dataType" => "select",
                "formElement" => "select",
                "visible" => "1",
                "required" => "0",
                "label" => "New Theme",
                "dataScope" => "custom_design",
                "source" => "schedule-design-update",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "custom_design",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ],
            "custom_layout" => [
                "dataType" => "select",
                "formElement" => "select",
                "options" => [
                    [
                        "value" => "",
                        "label" => "No layout updates"
                    ],
                    [
                        "label" => "Empty",
                        "value" => "empty"
                    ],
                    [
                        "label" => "1 column",
                        "value" => "1column"
                    ],
                    [
                        "label" => "2 columns with left bar",
                        "value" => "2columns-left"
                    ],
                    [
                        "label" => "2 columns with right bar",
                        "value" => "2columns-right"
                    ],
                    [
                        "label" => "3 columns",
                        "value" => "3columns"
                    ]
                ],
                "visible" => "1",
                "required" => "0",
                "label" => "New Layout",
                "dataScope" => "custom_layout",
                "source" => "schedule-design-update",
                "scopeLabel" => "[STORE VIEW]",
                "globalScope" => false,
                "code" => "custom_layout",
                "usedDefault" => true,
                "sortOrder" => "__placeholder__",
                "service" => [
                    "template" => "ui/form/element/helper/service"
                ],
                "componentType" => "field"
            ]
        ],
        "label" => "Schedule Design Update",
        "collapsible" => true,
        "dataScope" => "data.product",
        "sortOrder" => "__placeholder__",
        "componentType" => "fieldset"
    ]
];
