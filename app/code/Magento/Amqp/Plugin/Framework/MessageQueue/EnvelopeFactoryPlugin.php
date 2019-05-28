<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Amqp\Plugin\Framework\MessageQueue;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Plugin to set 'store_id' to the new custom header 'store_id' in amqp
 * 'application_headers'.
 */
class EnvelopeFactoryPlugin
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Pass current 'store_id' to the new custom header 'store_id' in amqp
     * 'application_headers' Magento\AsynchronousOperations\Model\MassConsumer
     * will use store_id to setCurrentStore and will execute messages for
     * correct store instead of default.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCreate(EnvelopeFactory $subject, array $data = [])
    {
        if (!isset($data['publisher_flag'])) {
            return null;
        } else {
            unset($data['publisher_flag']);
        }
        try {
            $storeId = $this->storeManager->getStore()->getId();

            if (isset($storeId)) {
                if (isset($data['properties'])) {
                    $properties = $data['properties'];
                    if (isset($properties['application_headers'])) {
                        $headers = $properties['application_headers'];
                        if ($headers instanceof AMQPTable) {
                            $headers->set('store_id', $storeId);
                            $data['properties']['application_headers'] = $headers;
                        }
                    } else {
                        $data['properties']['application_headers'] = new AMQPTable(['store_id' => $storeId]);
                    }
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return [$data];
    }
}
