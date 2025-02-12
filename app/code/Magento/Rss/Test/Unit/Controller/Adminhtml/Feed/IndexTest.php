<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Test\Unit\Controller\Adminhtml\Feed;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rss\Controller\Adminhtml\Feed\Index as AdminIndex;
use Magento\Rss\Model\Rss;
use Magento\Rss\Model\RssFactory;
use Magento\Rss\Model\RssManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var AdminIndex
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var RssManager|MockObject
     */
    protected $rssManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var MockObject
     */
    protected $rssFactory;

    /**
     * @var MockObject
     */
    protected $response;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->rssManager = $this->createPartialMock(RssManager::class, ['getProvider']);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->rssFactory = $this->createPartialMock(RssFactory::class, ['create']);

        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $request->expects($this->once())->method('getParam')->with('type')->willReturn('rss_feed');

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->onlyMethods(['sendResponse'])
            ->addMethods(['setHeader', 'setBody'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $controllerArguments = $objectManagerHelper->getConstructArguments(
            AdminIndex::class,
            [
                'rssManager' => $this->rssManager,
                'scopeConfig' => $this->scopeConfigInterface,
                'rssFactory' => $this->rssFactory,
                'request' => $request,
                'response' => $this->response
            ]
        );
        $objectManager = $controllerArguments['context']->getObjectManager();
        $urlInterface = $this->getMockForAbstractClass(UrlInterface::class);
        $objectManager
            ->method('get')
            ->with(UrlInterface::class)
            ->willReturn($urlInterface);
        $this->controller = $objectManagerHelper->getObject(
            AdminIndex::class,
            $controllerArguments
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->willReturn(true);
        $dataProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->willReturn(true);

        $rssModel = $this->createPartialMock(Rss::class, ['setDataProvider', 'createRssXml']);
        $rssModel->expects($this->once())->method('setDataProvider')->willReturnSelf();
        $rssModel->expects($this->once())->method('createRssXml')->willReturn('');

        $this->response->expects($this->once())->method('setHeader')->willReturnSelf();
        $this->response->expects($this->once())->method('setBody')->willReturnSelf();

        $this->rssFactory->expects($this->once())->method('create')->willReturn($rssModel);

        $this->rssManager->expects($this->once())->method('getProvider')->willReturn($dataProvider);
        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->willReturn(true);
        $dataProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->willReturn(true);

        $rssModel = $this->createPartialMock(Rss::class, ['setDataProvider', 'createRssXml']);
        $rssModel->expects($this->once())->method('setDataProvider')->willReturnSelf();

        $exceptionMock = new RuntimeException(
            new Phrase('Any message')
        );

        $rssModel->expects($this->once())->method('createRssXml')->willThrowException(
            $exceptionMock
        );

        $this->response->expects($this->once())->method('setHeader')->willReturnSelf();
        $this->rssFactory->expects($this->once())->method('create')->willReturn($rssModel);
        $this->rssManager->expects($this->once())->method('getProvider')->willReturn($dataProvider);

        $this->expectException(RuntimeException::class);
        $this->controller->execute();
    }
}
