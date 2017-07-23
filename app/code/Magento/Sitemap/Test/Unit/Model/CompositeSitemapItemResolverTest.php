<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Sitemap\Model\CompositeSitemapItemResolver;
use Magento\Sitemap\Model\SitemapItemInterface;
use Magento\Sitemap\Model\SitemapItemResolverInterface;

class CompositeSitemapItemResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testNoResolvers()
    {
        $resolver = new CompositeSitemapItemResolver();
        self::assertSame([], $resolver->getItems(1));
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
            $mockResolver = $this->getMockForAbstractClass(SitemapItemResolverInterface::class);
            $mockResolver->expects(self::once())
                ->method('getItems')
                ->willReturn($data);

            $mockResolvers[] = $mockResolver;
        }

        $resolver = new CompositeSitemapItemResolver($mockResolvers);
        $items = $resolver->getItems(1);

        self::assertSame($expectedItems, $items);
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
            for ($i = 1; $i < $maxProviders = random_int(1, 5); $i++) {
                $items = [];
                for ($i = 1; $i < $maxItems = random_int(2, 5); $i++) {
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
