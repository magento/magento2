<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\Batch;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\Batch\Observer;
use Magento\Sitemap\Model\Batch\Sitemap;
use Magento\Sitemap\Model\Batch\SitemapFactory;
use Magento\Sitemap\Model\EmailNotification;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for Magento\Sitemap\Model\Batch\Observer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends TestCase
{
    /**
     * @var Observer
     */
    private Observer $observer;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var SitemapFactory|MockObject
     */
    private $batchSitemapFactoryMock;

    /**
     * @var EmailNotification|MockObject
     */
    private $emailNotificationMock;

    /**
     * @var Emulation|MockObject
     */
    private $appEmulationMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->batchSitemapFactoryMock = $this->createMock(SitemapFactory::class);
        $this->emailNotificationMock = $this->createMock(EmailNotification::class);
        $this->appEmulationMock = $this->createMock(Emulation::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->observer = new Observer(
            $this->scopeConfigMock,
            $this->collectionFactoryMock,
            $this->batchSitemapFactoryMock,
            $this->emailNotificationMock,
            $this->appEmulationMock,
            $this->loggerMock
        );
    }

    /**
     * Test that no sitemaps are generated when the feature is disabled
     */
    public function testScheduledGenerateSitemapsWhenDisabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                'sitemap/generate/enabled',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(false);

        $this->collectionFactoryMock->expects($this->never())
            ->method('create');

        $this->observer->scheduledGenerateSitemaps();
    }

    /**
     * Test successful generation of sitemaps
     */
    public function testScheduledGenerateSitemapsSuccess(): void
    {
        $storeId = 1;
        $filename = 'sitemap.xml';
        $sitemapData = ['sitemap_id' => 1, 'store_id' => $storeId, 'filename' => $filename];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                'sitemap/generate/enabled',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);

        $collectionMock = $this->createMock(Collection::class);
        $sitemapMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getStoreId', 'getData'])
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$sitemapMock]));

        $sitemapMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $sitemapMock->expects($this->once())
            ->method('getData')
            ->willReturn($sitemapData);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $batchSitemapMock = $this->getMockBuilder(Sitemap::class)
            ->onlyMethods(['setData', 'generateXml'])
            ->addMethods(['getSitemapFilename'])
            ->disableOriginalConstructor()
            ->getMock();
        $batchSitemapMock->expects($this->once())
            ->method('setData')
            ->with($sitemapData)
            ->willReturnSelf();
        $batchSitemapMock->expects($this->once())
            ->method('generateXml');
        $batchSitemapMock->expects($this->once())
            ->method('getSitemapFilename')
            ->willReturn($filename);

        $this->batchSitemapFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($batchSitemapMock);

        $this->appEmulationMock->expects($this->once())
            ->method('startEnvironmentEmulation')
            ->with($storeId, Area::AREA_FRONTEND, true);
        $this->appEmulationMock->expects($this->once())
            ->method('stopEnvironmentEmulation');

        $this->loggerMock->expects($this->exactly(2))
            ->method('info');

        $this->observer->scheduledGenerateSitemaps();
    }

    /**
     * Test error handling during sitemap generation
     */
    public function testScheduledGenerateSitemapsWithError(): void
    {
        $storeId = 1;
        $errorRecipient = 'admin@example.com';
        $errorMessage = 'Generation failed';
        $sitemapData = ['sitemap_id' => 1, 'store_id' => $storeId, 'filename' => 'sitemap.xml'];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'sitemap/generate/error_email',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($errorRecipient);

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                'sitemap/generate/enabled',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);

        $collectionMock = $this->createMock(Collection::class);
        $sitemapMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getStoreId', 'getData'])
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$sitemapMock]));

        $sitemapMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $sitemapMock->expects($this->once())
            ->method('getData')
            ->willReturn($sitemapData);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $batchSitemapMock = $this->createMock(Sitemap::class);
        $this->batchSitemapFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($batchSitemapMock);
        $batchSitemapMock->expects($this->once())
            ->method('setData')
            ->with($sitemapData)
            ->willReturnSelf();
        $batchSitemapMock->expects($this->once())
            ->method('generateXml')
            ->willThrowException(new \Exception($errorMessage));

        $this->appEmulationMock->expects($this->once())
            ->method('startEnvironmentEmulation')
            ->with($storeId, Area::AREA_FRONTEND, true);
        $this->appEmulationMock->expects($this->once())
            ->method('stopEnvironmentEmulation');

        $this->loggerMock->expects($this->once())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $errorMessage,
                $this->callback(function ($context) {
                    return isset($context['exception']) && $context['exception'] instanceof \Exception;
                })
            );

        $this->emailNotificationMock->expects($this->once())
            ->method('sendErrors')
            ->with([$errorMessage]);

        $this->observer->scheduledGenerateSitemaps();
    }
}
