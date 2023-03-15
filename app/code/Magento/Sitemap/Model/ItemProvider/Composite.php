<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

class Composite implements ItemProviderInterface
{
    /**
     * Composite constructor.
     *
     * @param ItemProviderInterface[] $itemProviders Item resolvers
     */
    public function __construct(
        private $itemProviders = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $items = [];

        foreach ($this->itemProviders as $resolver) {
            foreach ($resolver->getItems($storeId) as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
