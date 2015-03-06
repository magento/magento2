<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\Object\Copy;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory as OrderAddressFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class ToOrderAddress
 */
class ToOrderAddress
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderAddressFactory
     */
    protected $orderAddressFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param OrderAddressFactory $orderAddressFactory
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderAddressFactory $orderAddressFactory,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderAddressFactory = $orderAddressFactory;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Address $object
     * @param array $data
     * @return OrderAddressInterface
     */
    public function convert(Address $object, $data = [])
    {
        $orderAddressData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_address',
            'to_order_address',
            $object
        );

        $orderAddress = $this->orderAddressFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderAddress,
            array_merge($orderAddressData, $data),
            '\Magento\Sales\Api\Data\OrderAddressInterface'
        );

        return $orderAddress;
    }
}
