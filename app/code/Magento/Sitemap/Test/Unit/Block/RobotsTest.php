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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Block\Robots;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Sitemap\Model\Sitemap;
use Magento\Sitemap\Model\SitemapConfigReader;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sitemap\Block\Robots.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RobotsTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private $sitemapCollectionFactory;

    /**
     * @var Robots
     */
    private $model;

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

    /**
     * @var SitemapConfigReader|MockObject
     */
    private $siteMapConfigReader;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $context = $this->createMock(Context::class);
        $context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->sitemapCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->siteMapConfigReader = $this->createMock(SitemapConfigReader::class);

        $this->model = $objectManager->getObject(
            Robots::class,
            [
                'context' => $context,
                'sitemapCollectionFactory' => $this->sitemapCollectionFactory,
                'storeManager' => $this->storeManager,
                'sitemapConfigReader' => $this->siteMapConfigReader
            ]
        );
    }

    /**
     * Check toHtml() method in case when robots submission is disabled
     *
     * @return void
     */
    public function testToHtmlRobotsSubmissionIsDisabled(): void
    {
        $defaultStoreId = 1;
        $expected = '';

        $this->initEventManagerMock($expected);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([$defaultStoreId]);

        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->with(null)
            ->willReturn($websiteMock);

        $this->siteMapConfigReader->expects($this->once())
            ->method('getEnableSubmissionRobots')
            ->with($defaultStoreId)
            ->willReturn(false);

        $this->assertEquals($expected, $this->model->toHtml());
    }

    /**
     * Check toHtml() method in case when robots submission is enabled
     *
     * @return void
     */
    public function testAfterGetDataRobotsSubmissionIsEnabled(): void
    {
        $defaultStoreId = 1;
        $secondStoreId = 2;

        $sitemapPath = '/';
        $sitemapFilenameOne = 'sitemap.xml';
        $sitemapFilenameTwo = 'sitemap_custom.xml';
        $sitemapFilenameThree = 'sitemap.xml';

        $expected = 'Sitemap: ' . $sitemapFilenameOne . PHP_EOL . 'Sitemap: ' . $sitemapFilenameTwo . PHP_EOL;

        $this->initEventManagerMock($expected);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([$defaultStoreId, $secondStoreId]);

        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->with(null)
            ->willReturn($websiteMock);

        $this->siteMapConfigReader->expects($this->atLeastOnce())
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
        $sitemapCollectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with([$defaultStoreId])
            ->willReturnSelf();

        $sitemapCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$sitemapMockOne, $sitemapMockTwo, $sitemapMockThree]));

        $this->sitemapCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($sitemapCollectionMock);

        $this->assertEquals($expected, $this->model->toHtml());
    }

    /**
     * Check that getIdentities() method returns specified cache tag
     *
     * @return void
     */
    public function testGetIdentities(): void
    {
        $storeId = 1;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->storeManager->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $expected = [Value::CACHE_TAG . '_' . $storeId];
        $this->assertEquals($expected, $this->model->getIdentities());
    }

    /**
     * Initialize mock object of Event Manager
     *
     * @param string $data
     * @return void
     */
    protected function initEventManagerMock($data): void
    {
        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->willReturnMap([
                [
                    'view_block_abstract_to_html_before',
                    ['block' => $this->model],
                ],
                [
                    'view_block_abstract_to_html_after',
                    ['block' => $this->model, 'transport' => new DataObject(['html' => $data])],
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
    protected function getSitemapMock($sitemapPath, $sitemapFilename): MockObject
    {
        $sitemapMock = $this->getMockBuilder(Sitemap::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSitemapUrl'])
            ->addMethods(['getSitemapFilename', 'getSitemapPath'])
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
