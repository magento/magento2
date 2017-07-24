<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Sitemap\Model\CmsPageSitemapItemResolver;
use Magento\Sitemap\Model\ResourceModel\Cms\Page;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

class CmsPageSitemapItemResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetItemsEmpty()
    {
        $storeConfigMock = $this->getStoreConfigMock([
            CmsPageSitemapItemResolver::XML_PATH_PAGE_CHANGEFREQ => 'daily',
            CmsPageSitemapItemResolver::XML_PATH_PAGE_PRIORITY => '1.0',
        ]);

        $cmsPageMock = $this->getCmsPageCollectionMock([]);
        $cmsPageFactoryMock = $this->getCmsPageFactoryMock($cmsPageMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CmsPageSitemapItemResolver($storeConfigMock, $cmsPageFactoryMock, $itemFactoryMock);
        $this->assertSame([], $resolver->getItems(1));
    }

    /**
     * @dataProvider pageProvider
     * @param array $pages
     */
    public function testGetItems(array $pages = [])
    {
        $storeConfigMock = $this->getStoreConfigMock([
            CmsPageSitemapItemResolver::XML_PATH_PAGE_CHANGEFREQ => 'daily',
            CmsPageSitemapItemResolver::XML_PATH_PAGE_PRIORITY => '1.0',
        ]);

        $cmsPageMock = $this->getCmsPageCollectionMock($pages);

        $cmsPageFactoryMock = $this->getCmsPageFactoryMock($cmsPageMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CmsPageSitemapItemResolver($storeConfigMock, $cmsPageFactoryMock, $itemFactoryMock);
        $items = $resolver->getItems(1);
        $this->assertTrue(count($items) == count($pages));
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
    public function pageProvider()
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getItemFactoryMock()
    {
        $itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->setMethods(['create'])
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCmsPageFactoryMock($returnValue)
    {
        $cmsPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $cmsPageFactoryMock->expects(self::any())
            ->method('create')
            ->willReturn($returnValue);

        return $cmsPageFactoryMock;
    }

    /**
     * @param array $pathMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStoreConfigMock(array $pathMap = [])
    {
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfigMock->method('getValue')
            ->willReturnCallback(function ($path) use ($pathMap) {
                return isset($pathMap[$path]) ? $pathMap[$path] : null;
            });

        return $scopeConfigMock;
    }

    /**
     * @param $returnValue
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCmsPageCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $sitemapCmsPageMock->expects(self::any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
