<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Sales\Model\Reorder;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Gets info buy request from order info interface and process custom options
 */
class OrderInfoBuyRequestGetter
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Prepare Custom Option for order Item by unserializing custom options data
     *
     * @param OrderItemInterface $orderItem
     * @return DataObject
     */
    public function getInfoBuyRequest(OrderItemInterface $orderItem): DataObject
    {
        $info = $orderItem->getProductOptionByCode('info_buyRequest');
        $options = $orderItem->getProductOptionByCode('options');

        if (!empty($options) && is_array($info)) {
            foreach ($options as $option) {
                if (array_key_exists($option['option_id'], $info['options'])) {
                    try {
                        $value = $this->serializer->unserialize($option['option_value']);
                        $info['options'][$option['option_id']] = $value;
                    } catch (\InvalidArgumentException $exception) {
                        $this->logger->warning($exception);
                    }
                }
            }
        }

        $infoBuyRequest = new DataObject($info);
        $infoBuyRequest->setQty($orderItem->getQtyOrdered());

        return $infoBuyRequest;
    }

}
