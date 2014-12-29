<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Sales\Api\Data\OrderDataBuilder as OrderBuilder;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class ToOrder converter
 */
class ToOrder
{
    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderBuilder;

    public function __construct(
        OrderBuilder $orderBuilder,
        \Magento\Framework\Object\Copy $objectCopyService
    ) {
        $this->orderBuilder = $orderBuilder;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param array $data
     * @return OrderInterface
     */
    public function convert(\Magento\Quote\Model\Quote\Address $object, $data = [])
    {
        $orderData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_order',
            $object
        );

        return $this->orderBuilder
            ->populateWithArray(array_merge($orderData, $data))
            ->create();
    }
}
