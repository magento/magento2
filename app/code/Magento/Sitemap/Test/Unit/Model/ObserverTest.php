<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\App\Area;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\EmailNotification;
use Magento\Store\Model\App\Emulation;

/**
 * Class ObserverTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Sitemap\Model\Observer
     */
    private $observer;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sitemapCollectionMock;

    /**
     * @var \Magento\Sitemap\Model\Sitemap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sitemapMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var Emulation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appEmulationMock;

    /**
     * @var EmailNotification|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailNotificationMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->sitemapCollectionMock = $this->createPartialMock(
            \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection::class,
            ['getIterator']
        );
        $this->sitemapMock = $this->createPartialMock(
            \Magento\Sitemap\Model\Sitemap::class,
            [
                'generateXml',
                'getStoreId',
            ]
        );
        $this->appEmulationMock = $this->createMock(Emulation::class);
        $this->emailNotificationMock = $this->createMock(EmailNotification::class);
        $this->objectManager = new ObjectManager($this);

        $this->observer = $this->objectManager->getObject(
            \Magento\Sitemap\Model\Observer::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'appEmulation' => $this->appEmulationMock,
                'emailNotification' => $this->emailNotificationMock
            ]
        );
    }

    public function testScheduledGenerateSitemapsSendsExceptionEmail()
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

        $this->sitemapMock->expects($this->at(0))
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->sitemapMock->expects($this->once())
            ->method('generateXml')
            ->willThrowException(new \Exception($exception));

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                \Magento\Sitemap\Model\Observer::XML_PATH_ERROR_RECIPIENT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn('error-recipient@example.com');

        $this->observer->scheduledGenerateSitemaps();
    }
}
