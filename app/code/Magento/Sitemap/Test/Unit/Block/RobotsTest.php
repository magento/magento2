<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RobotsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Store\Model\StoreResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeResolver;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sitemapCollectionFactory;

    /**
     * @var \Magento\Sitemap\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sitemapHelper;

    /**
     * @var \Magento\Sitemap\Block\Robots
     */
    private $block;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->storeResolver = $this->getMockBuilder(\Magento\Store\Model\StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sitemapCollectionFactory = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sitemapHelper = $this->getMockBuilder(\Magento\Sitemap\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->block = new \Magento\Sitemap\Block\Robots(
            $this->context,
            $this->storeResolver,
            $this->sitemapCollectionFactory,
            $this->sitemapHelper,
            $this->storeManager
        );
    }

    /**
     * Check toHtml() method in case when robots submission is disabled
     */
    public function testToHtmlRobotsSubmissionIsDisabled()
    {
        $defaultStoreId = 1;
        $defaultWebsiteId = 1;

        $expected = '';

        $this->initEventManagerMock($expected);
        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(false);

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMockForAbstractClass();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($defaultWebsiteId);

        $this->storeManager->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($defaultWebsiteId);

        $websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([$defaultStoreId]);

        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->with($defaultWebsiteId)
            ->willReturn($websiteMock);

        $this->sitemapHelper->expects($this->once())
            ->method('getEnableSubmissionRobots')
            ->with($defaultStoreId)
            ->willReturn(false);

        $this->assertEquals($expected, $this->block->toHtml());
    }

    /**
     * Check toHtml() method in case when robots submission is enabled
     */
    public function testAfterGetDataRobotsSubmissionIsEnabled()
    {
        $defaultStoreId = 1;
        $secondStoreId = 2;
        $defaultWebsiteId = 1;

        $sitemapPath = '/';
        $sitemapFilenameOne = 'sitemap.xml';
        $sitemapFilenameTwo = 'sitemap_custom.xml';
        $sitemapFilenameThree = 'sitemap.xml';

        $expected = 'Sitemap: ' . $sitemapFilenameOne
            . PHP_EOL
            . 'Sitemap: ' . $sitemapFilenameTwo
            . PHP_EOL;

        $this->initEventManagerMock($expected);
        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(false);

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMockForAbstractClass();

        $this->storeManager->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($defaultWebsiteId);

        $websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([$defaultStoreId, $secondStoreId]);

        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->with($defaultWebsiteId)
            ->willReturn($websiteMock);

        $this->sitemapHelper->expects($this->any())
            ->method('getEnableSubmissionRobots')
            ->willReturnMap([
                [$defaultStoreId, true],
                [$secondStoreId, false],
            ]);

        $sitemapMockOne = $this->getSitemapMock($sitemapPath, $sitemapFilenameOne);
        $sitemapMockTwo = $this->getSitemapMock($sitemapPath, $sitemapFilenameTwo);
        $sitemapMockThree = $this->getSitemapMock($sitemapPath, $sitemapFilenameThree);

        $sitemapCollectionMock = $this->getMockBuilder(\Magento\Sitemap\Model\ResourceModel\Sitemap\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sitemapCollectionMock->expects($this->any())
            ->method('addStoreFilter')
            ->with([$defaultStoreId])
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
     * Check that getIdentities() method returns specified cache tag
     */
    public function testGetIdentities()
    {
        $storeId = 1;

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMockForAbstractClass();

        $this->storeManager->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $expected = [
            \Magento\Robots\Model\Config\Value::CACHE_TAG . '_' . $storeId,
        ];
        $this->assertEquals($expected, $this->block->getIdentities());
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
     * @return \PHPUnit\Framework\MockObject\MockObject
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
