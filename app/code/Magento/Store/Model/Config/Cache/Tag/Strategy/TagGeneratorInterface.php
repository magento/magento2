<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Config\Cache\Tag\Strategy;

use Magento\Framework\App\Config\ValueInterface;

/**
 * Store configuration cache tag generator interface
 */
interface TagGeneratorInterface
{
    /**
     * Generate cache tags with given store configuration
     *
     * @param ValueInterface $config
     * @return array
     */
    public function generateTags(ValueInterface $config): array;
}
