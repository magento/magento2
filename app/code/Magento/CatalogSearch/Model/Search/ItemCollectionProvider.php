<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\Data\Collection;

/**
 * Search collection provider.
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @var array
     */
    private $factories;

    /**
     * ItemCollectionProvider constructor.
     * @param EngineResolverInterface $engineResolver
     * @param array $factories
     */
    public function __construct(
        EngineResolverInterface $engineResolver,
        array $factories
    ) {
        $this->engineResolver = $engineResolver;
        $this->factories = $factories;
    }

    /**
     * @inheritdoc
     */
    public function getCollection(): Collection
    {
        if (!isset($this->factories[$this->engineResolver->getCurrentSearchEngine()])) {
            throw new \DomainException('Undefined factory ' . $this->engineResolver->getCurrentSearchEngine());
        }
        return $this->factories[$this->engineResolver->getCurrentSearchEngine()]->create();
    }
}
