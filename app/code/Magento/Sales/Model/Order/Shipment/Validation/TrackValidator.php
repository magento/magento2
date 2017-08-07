<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment\Validation;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class TrackRequiredField
 * @since 2.1.2
 */
class TrackValidator implements ValidatorInterface
{
    /**
     * @param object|ShipmentInterface $entity
     * @return array
     * @since 2.1.2
     */
    public function validate($entity)
    {
        $messages = [];
        if (!$entity->getTracks()) {
            return $messages;
        }
        foreach ($entity->getTracks() as $track) {
            if (!$track->getTrackNumber()) {
                $messages[] = __('Please enter a tracking number.');
            }
        }
        return $messages;
    }
}
