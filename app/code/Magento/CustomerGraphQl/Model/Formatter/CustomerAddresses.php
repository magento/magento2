<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Formatter;

use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;

class CustomerAddresses
{
    /**
     * CustomerAddresses Constructor
     *
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     */
    public function __construct(
        private readonly ExtractCustomerAddressData $extractCustomerAddressData,
    ) {
    }

    /**
     * Format customer addressesV2
     *
     * @param AddressSearchResultsInterface $searchResult
     * @return array
     */
    public function format(AddressSearchResultsInterface $searchResult): array
    {
        $addressArray = [];
        foreach ($searchResult->getItems() as $address) {
            $addressArray[] = $this->extractCustomerAddressData->execute($address);
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $addressArray,
            'page_info' => [
                'page_size' => $searchResult->getSearchCriteria()->getPageSize(),
                'current_page' => $searchResult->getSearchCriteria()->getCurrentPage(),
                'total_pages' => (int)ceil($searchResult->getTotalCount()
                    / (int)$searchResult->getSearchCriteria()->getPageSize()),
            ]
        ];
    }
}
