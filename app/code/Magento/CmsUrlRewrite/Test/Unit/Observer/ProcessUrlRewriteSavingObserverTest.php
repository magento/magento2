<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Unit\Observer;

use Magento\Cms\Model\Page;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\CmsUrlRewrite\Observer\ProcessUrlRewriteSavingObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessUrlRewriteSavingObserverTest extends TestCase
{
    use MockCreationTrait;

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

        $this->urlPersistMock = $this->createMock(UrlPersistInterface::class);
        $this->cmsPageUrlRewriteGeneratorMock = $this->getMockBuilder(CmsPageUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageMock = $this->getMockBuilder(Page::class)
            ->onlyMethods(['getId', 'dataHasChangedFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getObject']
        );
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
     */
    #[DataProvider('executeDataProvider')]
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
    public static function executeDataProvider()
    {
        return  [
            ['identifierChanged' => true, 'storeIdChanged' => true],
            ['identifierChanged' => true, 'storeIdChanged' => false],
            ['identifierChanged' => false, 'storeIdChanged' => true],
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
