<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Block;

class RobotsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

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
     * @var \Magento\Sitemap\Block\Robots
     */
    private $block;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

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

        $this->block = new \Magento\Sitemap\Block\Robots(
            $this->context,
            $this->storeResolver,
            $this->sitemapCollectionFactory,
            $this->sitemapHelper
        );
    }

    /**
     * Check toHtml() method in case when robots submission is disabled
     */
    public function testToHtmlRobotsSubmissionIsDisabled()
    {
        $storeId = 1;

        $expected = '';

        $this->initEventManagerMock($expected);

        $this->storeResolver->expects($this->once())
            ->method('getCurrentStoreId')
            ->willReturn($storeId);

        $this->sitemapHelper->expects($this->once())
            ->method('getEnableSubmissionRobots')
            ->with($storeId)
            ->willReturn(false);

        $this->assertEquals($expected, $this->block->toHtml());
    }

    /**
     * Check toHtml() method in case when robots submission is enabled
     */
    public function testAfterGetDataRobotsSubmissionIsEnabled()
    {
        $storeId = 1;

        $sitemapPath = '/';
        $sitemapFilenameOne = 'sitemap.xml';
        $sitemapFilenameTwo = 'sitemap_custom.xml';
        $sitemapFilenameThree = 'sitemap.xml';

        $expected = ''
            . PHP_EOL
            . 'Sitemap: ' . $sitemapFilenameOne
            . PHP_EOL
            . 'Sitemap: ' . $sitemapFilenameTwo;

        $this->initEventManagerMock($expected);

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

        $this->assertEquals($expected, $this->block->toHtml());
    }

    /**
     * Initialize mock object of Event Manager
     *
     * @param string $data
     * @return void
     */
    protected function initEventManagerMock($data)
    {
        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->willReturnMap([
                [
                    'view_block_abstract_to_html_before',
                    [
                        'block' => $this->block,
                    ],
                ],
                [
                    'view_block_abstract_to_html_after',
                    [
                        'block' => $this->block,
                        'transport' => new \Magento\Framework\DataObject(['html' => $data]),
                    ],
                ],
            ]);
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
