<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ItemProvider\StoreUrl as StoreUrlItemResolver;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreUrlTest extends TestCase
{
    public function testGetItemData(): void
    {
        $resolver = new StoreUrlItemResolver($this->getConfigReaderMock(), $this->getItemFactoryMock());

        self::assertSame([
            'url' => '',
            'priority' => '1.0',
            'changeFrequency' => 'daily',
        ], $resolver->getItemData(1));
    }

    public function testGetItemsUsesOverriddenItemData(): void
    {
        $resolver = new class (
            $this->getConfigReaderMock(),
            $this->getItemFactoryMock()
        ) extends StoreUrlItemResolver {
            public function getItemData($storeId)
            {
                $data = parent::getItemData($storeId);
                $data['priority'] = '0.3';
                return $data;
            }
        };

        $items = $resolver->getItems(1);

        self::assertCount(1, $items);
        self::assertSame('', $items[0]->getUrl());
        self::assertSame('0.3', $items[0]->getPriority());
        self::assertSame('daily', $items[0]->getChangeFrequency());
        self::assertNull($items[0]->getUpdatedAt());
        self::assertNull($items[0]->getImages());
    }

    /**
     * test for getItems method
     */
    public function testGetItems()
    {
        $configReaderMock = $this->getConfigReaderMock();
        $itemFactoryMock = $this->getItemFactoryMock();
        $resolver = new StoreUrlItemResolver($configReaderMock, $itemFactoryMock);
        $items = $resolver->getItems(1);

        $this->assertCount(1, $items);
        foreach ($items as $item) {
            $this->assertSame('daily', $item->getChangeFrequency());
            $this->assertSame('1.0', $item->getPriority());
        }
    }

    /**
     * @return SitemapItemInterfaceFactory|MockObject
     */
    private function getItemFactoryMock()
    {
        $itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $helper = new ObjectManager($this);

                return $helper->getObject(SitemapItem::class, $data);
            });

        return $itemFactoryMock;
    }

    /**
     * @return ConfigReaderInterface|MockObject
     */
    private function getConfigReaderMock()
    {
        $configReaderMock = $this->createMock(ConfigReaderInterface::class);
        $configReaderMock->expects($this->any())
            ->method('getPriority')
            ->willReturn('1.0');
        $configReaderMock->expects($this->any())
            ->method('getChangeFrequency')
            ->willReturn('daily');

        return $configReaderMock;
    }
}
