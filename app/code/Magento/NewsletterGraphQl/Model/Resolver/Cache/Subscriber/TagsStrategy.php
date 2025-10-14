<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewsletterGraphQl\Model\Resolver\Cache\Subscriber;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Customer subscriber entity tag resolver strategy.
 */
class TagsStrategy implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        return [sprintf('%s_%s', "SUBSCRIBER", $object->getCustomerId())];
    }
}
