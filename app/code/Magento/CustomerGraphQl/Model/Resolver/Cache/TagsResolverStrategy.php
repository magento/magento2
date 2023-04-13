<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

/**
 * Cusotmer entity tag resolver strategy
 */
class TagsResolverStrategy implements \Magento\Framework\App\Cache\Tag\StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        return [sprintf('%s_%s', \Magento\Customer\Model\Customer::ENTITY, $object->getId())];
    }
}
