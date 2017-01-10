<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ObserverTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilderMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inlineTranslationMock;

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
        $this->transportBuilderMock = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(\Magento\Framework\Translate\Inline\StateInterface::class)
            ->getMock();
        $this->sitemapCollectionMock = $this->getMock(
            \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection::class,
            ['getIterator'],
            [],
            '',
            false
        );
        $this->sitemapMock = $this->getMock(\Magento\Sitemap\Model\Sitemap::class, ['generateXml'], [], '', false);

        $this->objectManager = new ObjectManager($this);
        $this->observer = $this->objectManager->getObject(
            \Magento\Sitemap\Model\Observer::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'transportBuilder' => $this->transportBuilderMock,
                'inlineTranslation' => $this->inlineTranslationMock
            ]
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testScheduledGenerateSitemapsThrowsException()
    {
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->willReturn(true);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->sitemapCollectionMock);

        $this->sitemapCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->sitemapMock]));

        $this->sitemapMock->expects($this->once())->method('generateXml')->willThrowException(new \Exception);

        $this->observer->scheduledGenerateSitemaps();
    }
}
