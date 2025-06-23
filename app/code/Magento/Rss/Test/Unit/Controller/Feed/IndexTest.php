<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Test\Unit\Controller\Feed;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rss\Controller\Feed\Index;
use Magento\Rss\Model\Rss;
use Magento\Rss\Model\RssFactory;
use Magento\Rss\Model\RssManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Rss\Controller\Feed\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    protected $controller;

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

    protected function setUp(): void
    {
        $this->rssManager = $this->createPartialMock(RssManager::class, ['getProvider']);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->rssFactory = $this->createPartialMock(RssFactory::class, ['create']);

        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $request->expects($this->once())->method('getParam')->with('type')->willReturn('rss_feed');

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setHeader', 'setBody'])
            ->onlyMethods(['sendResponse'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->controller = $objectManagerHelper->getObject(
            Index::class,
            [
                'rssManager' => $this->rssManager,
                'scopeConfig' => $this->scopeConfigInterface,
                'rssFactory' => $this->rssFactory,
                'request' => $request,
                'response' => $this->response
            ]
        );
    }

    public function testExecute()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->willReturn(true);
        $dataProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->willReturn(true);
        $dataProvider->expects($this->once())->method('isAuthRequired')->willReturn(false);

        $rssModel = $this->createPartialMock(Rss::class, ['setDataProvider', 'createRssXml']);
        $rssModel->expects($this->once())->method('setDataProvider')->willReturnSelf();
        $rssModel->expects($this->once())->method('createRssXml')->willReturn('');

        $matcher = $this->exactly(2);
        $this->response->expects($matcher)
            ->method('setHeader')
            ->willReturnCallback(function (string $param) use ($matcher) {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertEquals($param, 'Content-type'),
                    2 => $this->assertEquals($param, 'X-Magento-Tags'),
                };
            })
            ->willReturnSelf();
        $this->response->expects($this->once())->method('setBody')->willReturnSelf();

        $this->rssFactory->expects($this->once())->method('create')->willReturn($rssModel);

        $this->rssManager->expects($this->once())->method('getProvider')->willReturn($dataProvider);
        $this->controller->execute();
    }

    public function testExecuteWithException()
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

        $matcher = $this->exactly(2);
        $this->response->expects($matcher)
            ->method('setHeader')
            ->willReturnCallback(function (string $param) use ($matcher) {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertEquals($param, 'Content-type'),
                    2 => $this->assertEquals($param, 'X-Magento-Tags'),
                };
            })
            ->willReturnSelf();
        $this->rssFactory->expects($this->once())->method('create')->willReturn($rssModel);
        $this->rssManager->expects($this->once())->method('getProvider')->willReturn($dataProvider);

        $this->expectException(RuntimeException::class);
        $this->controller->execute();
    }
}
