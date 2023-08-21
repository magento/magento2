<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\EmailNotification;
use Magento\Sitemap\Model\Observer;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Sitemap\Model\Sitemap;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private $sitemapCollectionMock;

    /**
     * @var Sitemap|MockObject
     */
    private $sitemapMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Emulation|MockObject
     */
    private $appEmulationMock;

    /**
     * @var EmailNotification|MockObject
     */
    private $emailNotificationMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->sitemapCollectionMock = $this->createPartialMock(
            Collection::class,
            ['getIterator']
        );
        $this->sitemapMock = $this->getMockBuilder(Sitemap::class)
            ->addMethods(['getStoreId'])
            ->onlyMethods(['generateXml'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->appEmulationMock = $this->createMock(Emulation::class);
        $this->emailNotificationMock = $this->createMock(EmailNotification::class);
        $this->objectManager = new ObjectManager($this);

        $this->observer = $this->objectManager->getObject(
            Observer::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'appEmulation' => $this->appEmulationMock,
                'emailNotification' => $this->emailNotificationMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testScheduledGenerateSitemapsSendsExceptionEmail(): void
    {
        $exception = 'Sitemap Exception';
        $storeId = 1;

        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->willReturn(true);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->sitemapCollectionMock);

        $this->sitemapCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->sitemapMock]));

        $this->sitemapMock
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->sitemapMock->expects($this->once())
            ->method('generateXml')
            ->willThrowException(new \Exception($exception));

        $this->scopeConfigMock
            ->method('getValue')
            ->with(Observer::XML_PATH_ERROR_RECIPIENT, ScopeInterface::SCOPE_STORE)
            ->willReturn('error-recipient@example.com');

        $this->emailNotificationMock->expects($this->once())
            ->method('sendErrors')
            ->with([$exception]);

        $this->observer->scheduledGenerateSitemaps();
    }

    /**
     * Test if cron scheduled XML sitemap generation will start and stop the store environment emulation
     *
     * @return void
     * @throws \Exception
     */
    public function testCronGenerateSitemapEnvironmentEmulation(): void
    {
        $storeId = 1;

        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->willReturn(true);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->sitemapCollectionMock);

        $this->sitemapCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->sitemapMock]));

        $this->sitemapMock
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->sitemapMock->expects($this->once())
            ->method('generateXml');

        $this->appEmulationMock->expects($this->once())
            ->method('startEnvironmentEmulation');

        $this->appEmulationMock->expects($this->once())
            ->method('stopEnvironmentEmulation');

        $this->observer->scheduledGenerateSitemaps();
    }
}
