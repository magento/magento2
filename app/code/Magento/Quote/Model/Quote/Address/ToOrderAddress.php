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
    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderAddressBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderAddressBuilder;

    /**
     * @param OrderAddressBuilder $orderAddressBuilder
     * @param \Magento\Framework\Object\Copy $objectCopyService
     */
    public function __construct(
        OrderAddressBuilder $orderAddressBuilder,
        \Magento\Framework\Object\Copy $objectCopyService
    ) {
        $this->orderAddressBuilder = $orderAddressBuilder;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param array $data
     * @return OrderAddressInterface
     */
    public function convert(\Magento\Quote\Model\Quote\Address $object, $data = [])
    {
        $orderAddressData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_address',
            'to_order_address',
            $object
        );

        return $this->orderAddressBuilder
            ->populateWithArray($orderAddressData, $data)
            ->create();
    }
}
