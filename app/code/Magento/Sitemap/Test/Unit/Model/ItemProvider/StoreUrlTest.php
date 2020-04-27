<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
            ->setMethods(['create'])
            ->disableOriginalConstructor()
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
        $configReaderMock = $this->getMockForAbstractClass(ConfigReaderInterface::class);
        $configReaderMock->expects($this->any())
            ->method('getPriority')
            ->willReturn('1.0');
        $configReaderMock->expects($this->any())
            ->method('getChangeFrequency')
            ->willReturn('daily');

        return $configReaderMock;
    }
}
