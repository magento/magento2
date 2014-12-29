<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Sales\Api\Data\OrderAddressDataBuilder as OrderAddressBuilder;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class ToOrderAddress
 */
class ToOrderAddress
{
    protected $fields = [
        'prefix', 'firstname', 'middlename', 'lastname', 'suffix', 'company', 'street', 'street', 'city',
        'region', 'region_id', 'postcode', 'country_id', 'telephone', 'fax', 'email', 'customer_address_id'
    ];

    /**
     * @var OrderAddressBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderAddressBuilder;

    public function __construct(
        OrderAddressBuilder $orderAddressBuilder
    ) {
        $this->orderAddressBuilder = $orderAddressBuilder;
    }

    /**
     * @param array $data
     * @return OrderAddressInterface
     */
    public function convert(\Magento\Quote\Model\Quote\Address $object, $data = [])
    {
        return $this->orderAddressBuilder
            ->populateWithArray(array_merge(array_intersect_key($object->getData(), array_flip($this->fields)), $data))
            ->create();
    }
}
