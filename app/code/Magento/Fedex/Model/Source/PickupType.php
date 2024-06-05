<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Fedex\Model\Source;

/**
 * Fedex pickupType source implementation
 */
class PickupType extends \Magento\Fedex\Model\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'pickup_type';
}
