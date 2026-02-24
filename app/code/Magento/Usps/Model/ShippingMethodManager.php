<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Model;

class ShippingMethodManager
{
    /**
     * @var array
     */
    private array $shippingMethods = [
        "LIBRARY_MAIL_MACHINABLE_5-DIGIT" => [
            'description' => "Library Mail",
            'title' => "Library Mail",
            'rate_indicator' => "5D",
            'mail_class' => "LIBRARY_MAIL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "MEDIA_MAIL_MACHINABLE_5-DIGIT" => [
            'description' => "Media Mail Machinable 5-digit",
            'title' => "Media Mail",
            'rate_indicator' => "5D",
            'mail_class' => "MEDIA_MAIL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "USPS_GROUND_ADVANTAGE_MACHINABLE_SINGLE-PIECE" => [
            'description' => "USPS Ground Advantage Machinable Single-piece",
            'title' => "USPS Ground Advantage",
            'rate_indicator' => "SP",
            'mail_class' => "USPS_GROUND_ADVANTAGE",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_NON-SOFT_PACK_TIER_1" => [
            'description' => "USPS Ground Advantage Machinable Cubic Non-Soft Pack Tier 1",
            'title' => "USPS Ground Advantage Cubic Non-Soft Pack",
            'rate_indicator' => "CP",
            'mail_class' => "USPS_GROUND_ADVANTAGE",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_SOFT_PACK_TIER_1" => [
            'description' => "USPS Ground Advantage Machinable Cubic Soft Pack Tier 1",
            'title' => "USPS Ground Advantage Cubic Soft Pack",
            'rate_indicator' => "P5",
            'mail_class' => "USPS_GROUND_ADVANTAGE",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE" => [
            'description' => "Priority Mail Machinable Single-piece",
            'title' => "Priority Mail",
            'rate_indicator' => "SP",
            'mail_class' => "PRIORITY_MAIL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Flat Rate Envelope",
            'title' => "Priority Mail Flat Rate Envelope",
            'rate_indicator' => "FE",
            'mail_class' => "PRIORITY_MAIL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_MACHINABLE_MEDIUM_FLAT_RATE_BOX" => [
            'description' => "Priority Mail Machinable Medium Flat Rate Box",
            'title' => "Priority Mail Medium Flat Rate Box",
            'rate_indicator' => "FB",
            'mail_class' => "PRIORITY_MAIL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_MACHINABLE_LARGE_FLAT_RATE_BOX" => [
            'description' => "Priority Mail Machinable Large Flat Rate Box",
            'title' => "Priority Mail Large Flat Rate Box",
            'rate_indicator' => "PL",
            'mail_class' => "PRIORITY_MAIL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_MACHINABLE_SMALL_FLAT_RATE_BOX" => [
            'description' => "Priority Mail Machinable Small Flat Rate Box",
            'title' => "Priority Mail Small Flat Rate Box",
            'rate_indicator' => "FS",
            'mail_class' => "PRIORITY_MAIL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_PADDED_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Padded Flat Rate Envelope",
            'title' => "Priority Mail Padded Flat Rate Envelope",
            'rate_indicator' => "FP",
            'mail_class' => "PRIORITY_MAIL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_LEGAL_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Legal Flat Rate Envelope",
            'title' => "Priority Mail Legal Flat Rate Envelope",
            'rate_indicator' => "FA",
            'mail_class' => "PRIORITY_MAIL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_EXPRESS_MACHINABLE_SINGLE-PIECE" => [
            'description' => "Priority Mail Express Machinable Single-piece",
            'title' => "Priority Mail Express",
            'rate_indicator' => "PA",
            'mail_class' => "PRIORITY_MAIL_EXPRESS",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_EXPRESS_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Express Flat Rate Envelope",
            'title' => "Priority Mail Express Flat Rate Envelope",
            'rate_indicator' => "E4",
            'mail_class' => "PRIORITY_MAIL_EXPRESS",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_EXPRESS_LEGAL_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Express Legal Flat Rate Envelope",
            'title' => "Priority Mail Express Legal Flat Rate Envelope",
            'rate_indicator' => "E6",
            'mail_class' => "PRIORITY_MAIL_EXPRESS",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "NONE"
        ],
        "PRIORITY_MAIL_EXPRESS_PADDED_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Express Padded Flat Rate Envelope",
            'title' => "Priority Mail Express Padded Flat Rate Envelope",
            'rate_indicator' => "FP",
            'mail_class' => "PRIORITY_MAIL_EXPRESS",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "NONE"
        ],
        "FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE_MACHINABLE_ISC_SINGLE-PIECE" => [
            'description' => "First-Class Package International Service Machinable ISC Single-piece",
            'title' => "First-Class Package International Service",
            'rate_indicator' => "SP",
            'mail_class' => "FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 6,
                'height' => 4,
                'width' => 0.007
            ],
            'max_dimension' => [
                'length' => 24
            ]
        ],
        "PRIORITY_MAIL_INTERNATIONAL_ISC_SINGLE-PIECE" => [
            'description' => "Priority Mail International ISC Single-piece",
            'title' => "Priority Mail International",
            'rate_indicator' => "SP",
            'mail_class' => "PRIORITY_MAIL_INTERNATIONAL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 5.5,
                'height' => 3.5,
                'width' => 0.007
            ],
            'max_dimension' => [
                'length' => 60
            ]
        ],
        "PRIORITY_MAIL_INTERNATIONAL_ISC_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail International ISC Flat Rate Envelope",
            'title' => "Priority Mail International Flat Rate Envelope",
            'rate_indicator' => "FE",
            'mail_class' => "PRIORITY_MAIL_INTERNATIONAL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 12.5,
                'height' => 9.5,
                'width' => 0.007
            ]
        ],
        "PRIORITY_MAIL_INTERNATIONAL_MACHINABLE_ISC_MEDIUM_FLAT_RATE_BOX" => [
            'description' => "Priority Mail International Machinable ISC Medium Flat Rate Box",
            'title' => "Priority Mail International Medium Flat Rate Box",
            'rate_indicator' => "FB",
            'mail_class' => "PRIORITY_MAIL_INTERNATIONAL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 11.25,
                'height' => 8.75,
                'width' => 6
            ]
        ],
        "PRIORITY_MAIL_INTERNATIONAL_MACHINABLE_ISC_LARGE_FLAT_RATE_BOX" => [
            'description' => "Priority Mail International Machinable ISC Large Flat Rate Box",
            'title' => "Priority Mail International Large Flat Rate Box",
            'rate_indicator' => "PL",
            'mail_class' => "PRIORITY_MAIL_INTERNATIONAL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 12.25,
                'height' => 12.25,
                'width' => 6
            ]
        ],
        "PRIORITY_MAIL_INTERNATIONAL_MACHINABLE_ISC_SMALL_FLAT_RATE_BOX" => [
            'description' => "Priority Mail International Machinable ISC Small Flat Rate Box",
            'title' => "Priority Mail International Small Flat Rate Box",
            'rate_indicator' => "FS",
            'mail_class' => "PRIORITY_MAIL_INTERNATIONAL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 8.6875,
                'height' => 5.4375,
                'width' => 1.75
            ]
        ],
        "PRIORITY_MAIL_INTERNATIONAL_ISC_LEGAL_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail International ISC Legal Flat Rate Envelope",
            'title' => "Priority Mail International Legal Flat Rate Envelope",
            'rate_indicator' => "FA",
            'mail_class' => "PRIORITY_MAIL_INTERNATIONAL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 15,
                'height' => 9.5,
                'width' => 0.75
            ]
        ],
        "PRIORITY_MAIL_INTERNATIONAL_MACHINABLE_ISC_PADDED_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail International Machinable ISC Padded Flat Rate Envelope",
            'title' => "Priority Mail International Padded Flat Rate Envelope",
            'rate_indicator' => "FP",
            'mail_class' => "PRIORITY_MAIL_INTERNATIONAL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 12.5,
                'height' => 9.5,
                'width' => 1
            ]
        ],
        "PRIORITY_MAIL_EXPRESS_INTERNATIONAL_ISC_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Express International ISC Flat Rate Envelope",
            'title' => "Priority Mail Express International Flat Rate Envelope",
            'rate_indicator' => "E4",
            'mail_class' => "PRIORITY_MAIL_EXPRESS_INTERNATIONAL",
            'processing_category' => "MACHINABLE",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 12.5,
                'height' => 9.5,
                'width' => 0.5
            ]
        ],
        "PRIORITY_MAIL_EXPRESS_INTERNATIONAL_ISC_LEGAL_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Express International ISC Legal Flat Rate Envelope",
            'title' => "Priority Mail Express International Legal Flat Rate Envelope",
            'rate_indicator' => "E6",
            'mail_class' => "PRIORITY_MAIL_EXPRESS_INTERNATIONAL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 15,
                'height' => 9.5,
                'width' => 0.75
            ]
        ],
        "PRIORITY_MAIL_EXPRESS_INTERNATIONAL_ISC_PADDED_FLAT_RATE_ENVELOPE" => [
            'description' => "Priority Mail Express International ISC Padded Flat Rate Envelope",
            'title' => "Priority Mail Express International Padded Flat Rate Envelope",
            'rate_indicator' => "FP",
            'mail_class' => "PRIORITY_MAIL_EXPRESS_INTERNATIONAL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 12.5,
                'height' => 9.5,
                'width' => 1
            ]
        ],
        "PRIORITY_MAIL_EXPRESS_INTERNATIONAL_ISC_SINGLE-PIECE" => [
            'description' => "Priority Mail Express International ISC Single-piece",
            'title' => "Priority Mail Express International",
            'rate_indicator' => "PA",
            'mail_class' => "PRIORITY_MAIL_EXPRESS_INTERNATIONAL",
            'processing_category' => "FLATS",
            'destination_entry_facility_type' => "INTERNATIONAL_SERVICE_CENTER",
            'min_dimension' => [
                'length' => 6,
                'height' => 0.25,
                'width' => 0.25
            ],
            'max_dimension' => [
                'length' => 36
            ]
        ]
    ];

    /**
     * Get an array of all method codes with their titles
     *
     * @return array [method_code => title]
     */
    public function getMethodCodesWithTitles(): array
    {
        $result = [];
        foreach ($this->shippingMethods as $code => $data) {
            $result[$code] = $data['title'];
        }
        return $result;
    }

    /**
     * Get the rate indicator for a specific method code
     *
     * @param string $methodCode The shipping method code
     * @return string|null The rate indicator or null if not found
     */
    public function getRateIndicator(string $methodCode): ?string
    {
        $methodCode = strtoupper($methodCode);
        return $this->shippingMethods[$methodCode]['rate_indicator'] ?? 'SP';
    }

    /**
     * Get the mail class for a specific method code
     *
     * @param string $methodCode
     * @return string|null
     */
    public function getMethodTitle(string $methodCode): ?string
    {
        $methodCode = strtoupper($methodCode);
        return $this->shippingMethods[$methodCode]['title'] ?? null;
    }

    /**
     * Get the mail class for a specific method code
     *
     * @param string $methodCode
     * @return string|null
     */
    public function getMethodMailClass(string $methodCode): ?string
    {
        $methodCode = strtoupper($methodCode);
        return $this->shippingMethods[$methodCode]['mail_class'] ?? null;
    }

    /**
     * Get the processing category for a specific method code
     *
     * @param string $methodCode
     * @return string|null
     */
    public function getMethodProcessingCategory(string $methodCode): ?string
    {
        $methodCode = strtoupper($methodCode);
        return $this->shippingMethods[$methodCode]['processing_category'] ?? null;
    }

    /**
     * Get the destination entry facility type for a specific method code
     *
     * @param string $methodCode
     * @return string|null
     */
    public function getMethodDestinationEntryFacilityType(string $methodCode): ?string
    {
        $methodCode = strtoupper($methodCode);
        return $this->shippingMethods[$methodCode]['destination_entry_facility_type'] ?? 'NONE';
    }
    /**
     * Get the minimum dimensions for a specific method code
     *
     * @param string $methodCode
     * @return array|null
     */
    public function getMethodMinDimensions(string $methodCode): ?array
    {
        $methodCode = strtoupper($methodCode);
        return $this->shippingMethods[$methodCode]['min_dimension'] ?? null;
    }

    /**
     * Get the maximum dimensions for a specific method code
     *
     * @param string $methodCode
     * @return array|null
     */
    public function getMethodMaxDimensions(string $methodCode): ?array
    {
        $methodCode = strtoupper($methodCode);
        return $this->shippingMethods[$methodCode]['max_dimension'] ?? null;
    }
}
