<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Block\Robots;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Sitemap\Model\Sitemap;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RobotsTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var StoreResolver|MockObject
     */
    private $storeResolver;

    /**
     * @var CollectionFactory|MockObject
     */
    private $sitemapCollectionFactory;

    /**
     * @var Data|MockObject
     */
    private $sitemapHelper;

    /**
     * @var Robots
     */
    private $block;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->storeResolver = $this->getMockBuilder(StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sitemapCollectionFactory = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sitemapHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->block = new Robots(
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

        $storeMock = $this->getMockBuilder(StoreInterface::class)
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

        $websiteMock = $this->getMockBuilder(Website::class)
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

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $this->storeManager->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($defaultWebsiteId);

        $websiteMock = $this->getMockBuilder(Website::class)
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

        $sitemapCollectionMock = $this->getMockBuilder(Collection::class)
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

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $this->storeManager->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $expected = [
            Value::CACHE_TAG . '_' . $storeId,
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
                        'transport' => new DataObject(['html' => $data]),
                    ],
                ],
            ]);
    }

    /**
     * Create and return mock object of \Magento\Sitemap\Model\Sitemap class
     *
     * @param string $sitemapPath
     * @param string $sitemapFilename
     * @return MockObject
     */
    protected function getSitemapMock($sitemapPath, $sitemapFilename)
    {
        $sitemapMock = $this->getMockBuilder(Sitemap::class)
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
