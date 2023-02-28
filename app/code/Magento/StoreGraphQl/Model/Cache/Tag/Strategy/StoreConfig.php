<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Store\Model\Config\Cache\Tag\Strategy\TagGeneratorInterface;

/**
 * Produce cache tags for store config.
 */
class StoreConfig implements StrategyInterface
{
    /**
     * @var \Magento\Store\Model\Config\Cache\Tag\Strategy\TagGeneratorInterface
     */
    private $tagGenerator;

    public function __construct(
        TagGeneratorInterface $tagGenerator
    ) {
        $this->tagGenerator = $tagGenerator;
    }

    /**
     * @inheritdoc
     */
    public function getTags($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if ($object instanceof ValueInterface && $object->isValueChanged()) {
            return $this->tagGenerator->generateTags($object);
        }

        return [];
    }
}
