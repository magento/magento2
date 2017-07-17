<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;


class CompositeSitemapItemResolver implements SitemapItemResolverInterface
{
    /**
     * Item resolvers
     *
     * @var SitemapItemResolverInterface[]
     */
    private $itemResolvers;

    /**
     * CompositeSitemapItemResolver constructor.
     *
     * @param SitemapItemResolverInterface[] $itemResolvers
     */
    public function __construct($itemResolvers = [])
    {
        $this->itemResolvers = $itemResolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $items = [];

        foreach ($this->itemResolvers as $resolver) {
            foreach ($resolver->getItems($storeId) as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}