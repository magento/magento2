<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\Composite as CompositeItemResolver;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterface;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    public function testNoResolvers()
    {
        $resolver = new CompositeItemResolver();
        $this->assertSame([], $resolver->getItems(1));
    }

    /**
     * @dataProvider sitemapItemsProvider
     * @param array $itemResolverData
     * @param array $expectedItems
     */
    public function testGetItems($itemResolverData, $expectedItems)
    {
        $mockResolvers = [];

        foreach ($itemResolverData as $data) {
            $mockResolver = $this->getMockForAbstractClass(ItemProviderInterface::class);
            $mockResolver->expects(self::once())
                ->method('getItems')
                ->willReturn($data);

            $mockResolvers[] = $mockResolver;
        }

        $resolver = new CompositeItemResolver($mockResolvers);
        $items = $resolver->getItems(1);

        $this->assertSame($expectedItems, $items);
    }

    /**
     * @return array
     */
    public function sitemapItemsProvider()
    {
        $testCases = [];

        for ($i = 1; $i < 5; $i++) {
            $itemProviders = [];
            $expectedItems = [];
            $maxProviders = random_int(1, 5);
            for ($j = 1; $j < $maxProviders; $j++) {
                $items = [];
                $maxItems = random_int(2, 5);
                for ($k = 1; $k < $maxItems; $k++) {
                    $sitemapItem = $this->getMockForAbstractClass(SitemapItemInterface::class);
                    $items[] = $sitemapItem;
                    $expectedItems[]  = $sitemapItem;
                }
                $itemProviders[] = $items;
            }

            $testCases[] = [$itemProviders, $expectedItems];
        }

        return $testCases;
    }
}
