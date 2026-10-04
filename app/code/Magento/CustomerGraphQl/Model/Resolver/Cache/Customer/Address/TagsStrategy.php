<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache\Customer\Address;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Provides the customer record identity to invalidate on address change.
 */
class TagsStrategy implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        return [sprintf('%s_%s', Customer::ENTITY, $object->getCustomerId())];
    }
}
