<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Unit\Observer;

use Magento\Cms\Model\Page;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\CmsUrlRewrite\Observer\ProcessUrlRewriteSavingObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessUrlRewriteSavingObserverTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var UrlPersistInterface|MockObject
     */
    protected $urlPersistMock;

    /**
     * @var CmsPageUrlRewriteGenerator|MockObject
     */
    protected $cmsPageUrlRewriteGeneratorMock;

    /**
     * @var EventObserver|MockObject
     */
    protected $eventObserverMock;

    /**
     * @var Page|MockObject
     */
    protected $pageMock;

    /**
     * @var Event|MockObject
     */
    protected $eventMock;

    /**
     * @var ProcessUrlRewriteSavingObserver
     */
    protected $observer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->urlPersistMock = $this->getMockBuilder(UrlPersistInterface::class)
            ->getMockForAbstractClass();
        $this->cmsPageUrlRewriteGeneratorMock = $this->getMockBuilder(CmsPageUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['getId', 'dataHasChangedFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventObserverMock = $this->getMockBuilder(EventObserver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventObserverMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())
            ->method('getObject')
            ->willReturn($this->pageMock);

        $this->observer = $this->objectManagerHelper->getObject(
            ProcessUrlRewriteSavingObserver::class,
            [
                'cmsPageUrlRewriteGenerator' => $this->cmsPageUrlRewriteGeneratorMock,
                'urlPersist' => $this->urlPersistMock,
            ]
        );
    }

    /**
     * @param bool $identifierChanged
     * @param bool $storeIdChanged
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($identifierChanged, $storeIdChanged)
    {
        $pageId = 1;
        $urls = ['first url', 'second url'];

        $this->pageMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturnMap([
                ['identifier', $identifierChanged],
                ['store_id', $storeIdChanged],
            ]);
        $this->pageMock->expects($this->once())
            ->method('getId')
            ->willReturn($pageId);
        $this->cmsPageUrlRewriteGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($this->pageMock)
            ->willReturn($urls);
        $this->urlPersistMock->expects($this->once())
            ->method('deleteByData')
            ->with([
                UrlRewrite::ENTITY_ID => $pageId,
                UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE,
            ]);
        $this->urlPersistMock->expects($this->once())
            ->method('replace')
            ->with($urls);

        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * return array
     */
    public function executeDataProvider()
    {
        return  [
            ['identifier' => true, 'storeIdChanged' => true],
            ['identifier' => true, 'storeIdChanged' => false],
            ['identifier' => false, 'storeIdChanged' => true],
        ];
    }

    /**
     * @return void
     */
    public function testExecuteWithoutDataChanged()
    {
        $this->pageMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturnMap([
                ['identifier', false],
                ['store_id', false],
            ]);
        $this->pageMock->expects($this->never())
            ->method('getId');
        $this->cmsPageUrlRewriteGeneratorMock->expects($this->never())
            ->method('generate');
        $this->urlPersistMock->expects($this->never())
            ->method('deleteByData');
        $this->urlPersistMock->expects($this->never())
            ->method('replace');

        $this->observer->execute($this->eventObserverMock);
    }
}
