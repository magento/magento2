<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Theme;

use InvalidArgumentException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;

/**
 * Store associated themes resolver.
 */
class StoreThemesResolver implements StoreThemesResolverInterface
{
    /**
     * @var StoreThemesResolverInterface[]
     */
    private $resolvers;

    /**
     * @param StoreThemesResolverInterface[] $resolvers
     */
    public function __construct(
        array $resolvers
    ) {
        foreach ($resolvers as $resolver) {
            if (!$resolver instanceof StoreThemesResolverInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Instance of %s is expected, got %s instead.',
                        StoreThemesResolverInterface::class,
                        get_class($resolver)
                    )
                );
            }
        }
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritDoc
     */
    public function getThemes(StoreInterface $store): array
    {
        $themes = [];
        foreach ($this->resolvers as $resolver) {
            foreach ($resolver->getThemes($store) as $theme) {
                $themes[] = $theme;
            }
        }
        return array_values(array_unique($themes));
    }
}
