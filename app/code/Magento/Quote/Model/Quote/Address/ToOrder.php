<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\Object\Copy;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderDataBuilder as OrderBuilder;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class ToOrder converter
 */
class ToOrder
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderBuilder;

    /**
     * @param OrderBuilder $orderBuilder
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderBuilder $orderBuilder,
        Copy $objectCopyService
    ) {
        $this->orderBuilder = $orderBuilder;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param Address $object
     * @param array $data
     * @return OrderInterface
     */
    public function convert(Address $object, $data = [])
    {
        $orderData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_address',
            'to_order',
            $object
        );

        return $this->orderBuilder
            ->populateWithArray(array_merge($orderData, $data))
            ->setStoreId($object->getQuote()->getStoreId())
            ->setQuoteId($object->getQuote()->getId())
            ->create();
    }
}
