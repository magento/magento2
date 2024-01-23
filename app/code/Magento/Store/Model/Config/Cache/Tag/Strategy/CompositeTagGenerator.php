<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Config\Cache\Tag\Strategy;

use Magento\Framework\App\Config\ValueInterface;

/**
 * Composite tag generator that generates cache tags for store configurations.
 */
class CompositeTagGenerator implements TagGeneratorInterface
{
    /**
     * @var TagGeneratorInterface[]
     */
    private $tagGenerators;

    /**
     * @param TagGeneratorInterface[] $tagGenerators
     */
    public function __construct(
        array $tagGenerators = []
    ) {
        $this->tagGenerators = $tagGenerators;
    }

    /**
     * @inheritdoc
     */
    public function generateTags(ValueInterface $config): array
    {
        $tagsArray = [];
        foreach ($this->tagGenerators as $tagGenerator) {
            $tagsArray[] = $tagGenerator->generateTags($config);
        }
        return array_merge(...$tagsArray);
    }
}
