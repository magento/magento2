<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

class Composite implements ItemProviderInterface
{
    /**
     * Item resolvers
     *
     * @var ItemProviderInterface[]
     */
    private $itemProviders;

    /**
     * Composite constructor.
     *
     * @param ItemProviderInterface[] $itemProviders
     */
    public function __construct($itemProviders = [])
    {
        $this->itemProviders = $itemProviders;
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
