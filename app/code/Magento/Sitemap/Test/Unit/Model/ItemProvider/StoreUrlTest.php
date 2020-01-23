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

class StoreUrlTest extends \PHPUnit\Framework\TestCase
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
        
        $this->assertTrue(count($items) == 1);
        foreach ($items as $index => $item) {
            $this->assertSame('daily', $items[$index]->getChangeFrequency());
            $this->assertSame('1.0', $items[$index]->getPriority());
        }
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
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
     * @return \PHPUnit_Framework_MockObject_MockObject
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