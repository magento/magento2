<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ItemProvider\CmsPage as CmsPageItemResolver;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ResourceModel\Cms\Page as CmsPageResource;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CmsPageTest extends TestCase
{
    public function testGetItemsEmpty()
    {
        $configReaderMock = $this->getConfigReaderMock();
        $cmsPageMock = $this->getCmsPageCollectionMock([]);
        $cmsPageFactoryMock = $this->getCmsPageFactoryMock($cmsPageMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CmsPageItemResolver($configReaderMock, $cmsPageFactoryMock, $itemFactoryMock);

        $this->assertSame([], $resolver->getItems(1));
    }

    /**
     * @dataProvider pageProvider
     * @param array $pages
     */
    public function testGetItems(array $pages = [])
    {
        $configReaderMock = $this->getConfigReaderMock();
        $cmsPageMock = $this->getCmsPageCollectionMock($pages);
        $cmsPageFactoryMock = $this->getCmsPageFactoryMock($cmsPageMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CmsPageItemResolver($configReaderMock, $cmsPageFactoryMock, $itemFactoryMock);
        $items = $resolver->getItems(1);

        $this->assertCount(count($pages), $items);
        foreach ($pages as $index => $page) {
            $this->assertSame($page->getUpdatedAt(), $items[$index]->getUpdatedAt());
            $this->assertSame('daily', $items[$index]->getChangeFrequency());
            $this->assertSame('1.0', $items[$index]->getPriority());
            $this->assertSame($page->getImages(), $items[$index]->getImages());
            $this->assertSame($page->getUrl(), $items[$index]->getUrl());
        }
    }

    /**
     * @return array
     */
    public static function pageProvider()
    {
        return [
            [
                [
                    new DataObject([
                        'url' => 'http://dummy.url',
                        'id' => '/url',
                        'updated_at' => '2017-01-01 23:59:59'
                    ])
                ]
            ]
        ];
    }

    /**
     * @return MockObject
     */
    private function getItemFactoryMock()
    {
        $itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemFactoryMock->expects(self::any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $helper = new ObjectManager($this);

                return $helper->getObject(SitemapItem::class, $data);
            });

        return $itemFactoryMock;
    }

    /**
     * @param $returnValue
     * @return MockObject
     */
    private function getCmsPageFactoryMock($returnValue)
    {
        $cmsPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $cmsPageFactoryMock->expects(self::any())
            ->method('create')
            ->willReturn($returnValue);

        return $cmsPageFactoryMock;
    }

    /**
     * @return MockObject
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

    /**
     * @param $returnValue
     * @return MockObject
     */
    private function getCmsPageCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(CmsPageResource::class)
            ->onlyMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $sitemapCmsPageMock->expects(self::any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
