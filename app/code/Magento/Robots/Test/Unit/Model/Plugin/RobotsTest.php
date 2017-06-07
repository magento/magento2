<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Test\Unit\Model\Plugin;

class RobotsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeResolver;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sitemapCollectionFactory;

    /**
     * @var \Magento\Sitemap\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sitemapHelper;

    /**
     * @var \Magento\Sitemap\Model\Plugin\Robots
     */
    private $plugin;

    protected function setUp()
    {
        $this->storeResolver = $this->getMockBuilder(\Magento\Store\Model\StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sitemapCollectionFactory = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->sitemapHelper = $this->getMockBuilder(\Magento\Sitemap\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new \Magento\Sitemap\Model\Plugin\Robots(
            $this->storeResolver,
            $this->sitemapCollectionFactory,
            $this->sitemapHelper
        );
    }

    /**
     * Check afterGetData() method in case when robots submission is disabled
     */
    public function testAfterGetDataRobotsSubmissionIsDisabled()
    {
        $storeId = 1;
        $result = 'test';

        $this->storeResolver->expects($this->once())
            ->method('getCurrentStoreId')
            ->willReturn($storeId);

        $this->sitemapHelper->expects($this->once())
            ->method('getEnableSubmissionRobots')
            ->with($storeId)
            ->willReturn(false);

        $robotsDataMock = $this->getMockBuilder(\Magento\Robots\Model\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($result, $this->plugin->afterGetData($robotsDataMock, $result));
    }

    /**
     * Check afterGetData() method in case when robots submission is enabled
     */
    public function testAfterGetDataRobotsSubmissionIsEnabled()
    {
        $storeId = 1;
        $result = 'test';
        $sitemapPath = '/';
        $sitemapFilenameOne = 'sitemap.xml';
        $sitemapFilenameTwo = 'sitemap_custom.xml';
        $sitemapFilenameThree = 'sitemap.xml';

        $this->storeResolver->expects($this->once())
            ->method('getCurrentStoreId')
            ->willReturn($storeId);

        $this->sitemapHelper->expects($this->once())
            ->method('getEnableSubmissionRobots')
            ->with($storeId)
            ->willReturn(true);

        $sitemapMockOne = $this->getSitemapMock($sitemapPath, $sitemapFilenameOne);
        $sitemapMockTwo = $this->getSitemapMock($sitemapPath, $sitemapFilenameTwo);
        $sitemapMockThree = $this->getSitemapMock($sitemapPath, $sitemapFilenameThree);

        $sitemapCollectionMock = $this->getMockBuilder(\Magento\Sitemap\Model\ResourceModel\Sitemap\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sitemapCollectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with([$storeId])
            ->willReturnSelf();

        $sitemapCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$sitemapMockOne, $sitemapMockTwo, $sitemapMockThree]));

        $this->sitemapCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($sitemapCollectionMock);

        $robotsDataMock = $this->getMockBuilder(\Magento\Robots\Model\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expected = $result
            . PHP_EOL
            . 'Sitemap: ' . $sitemapFilenameOne
            . PHP_EOL
            . 'Sitemap: ' . $sitemapFilenameTwo;

        $this->assertEquals($expected, $this->plugin->afterGetData($robotsDataMock, $result));
    }

    /**
     * Create and return mock object of \Magento\Sitemap\Model\Sitemap class
     *
     * @param string $sitemapPath
     * @param string $sitemapFilename
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSitemapMock($sitemapPath, $sitemapFilename)
    {
        $sitemapMock = $this->getMockBuilder(\Magento\Sitemap\Model\Sitemap::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getSitemapFilename',
                'getSitemapPath',
                'getSitemapUrl',
            ])
            ->getMock();

        $sitemapMock->expects($this->any())
            ->method('getSitemapFilename')
            ->willReturn($sitemapFilename);
        $sitemapMock->expects($this->any())
            ->method('getSitemapPath')
            ->willReturn($sitemapPath);
        $sitemapMock->expects($this->any())
            ->method('getSitemapUrl')
            ->with($sitemapPath, $sitemapFilename)
            ->willReturn($sitemapFilename);

        return $sitemapMock;
    }
}
