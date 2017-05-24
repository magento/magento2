<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\Controller\Feed;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class IndexTest
 * @package Magento\Rss\Controller\Feed
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rss\Controller\Feed\Index
     */
    protected $controller;

    /**
     * @var \Magento\Rss\Model\RssManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    protected function setUp()
    {
        $this->rssManager = $this->getMock(\Magento\Rss\Model\RssManager::class, ['getProvider'], [], '', false);
        $this->scopeConfigInterface = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->rssFactory = $this->getMock(\Magento\Rss\Model\RssFactory::class, ['create'], [], '', false);

        $request = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $request->expects($this->once())->method('getParam')->with('type')->will($this->returnValue('rss_feed'));

        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setHeader', 'setBody', 'sendResponse'])
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Rss\Controller\Feed\Index::class,
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
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->will($this->returnValue(true));
        $dataProvider = $this->getMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $dataProvider->expects($this->once())->method('isAuthRequired')->will($this->returnValue(false));

        $rssModel = $this->getMock(\Magento\Rss\Model\Rss::class, ['setDataProvider', 'createRssXml'], [], '', false);
        $rssModel->expects($this->once())->method('setDataProvider')->will($this->returnSelf());
        $rssModel->expects($this->once())->method('createRssXml')->will($this->returnValue(''));

        $this->response->expects($this->once())->method('setHeader')->will($this->returnSelf());
        $this->response->expects($this->once())->method('setBody')->will($this->returnSelf());

        $this->rssFactory->expects($this->once())->method('create')->will($this->returnValue($rssModel));

        $this->rssManager->expects($this->once())->method('getProvider')->will($this->returnValue($dataProvider));
        $this->controller->execute();
    }

    public function testExecuteWithException()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->will($this->returnValue(true));
        $dataProvider = $this->getMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        $rssModel = $this->getMock(\Magento\Rss\Model\Rss::class, ['setDataProvider', 'createRssXml'], [], '', false);
        $rssModel->expects($this->once())->method('setDataProvider')->will($this->returnSelf());

        $exceptionMock = new \Magento\Framework\Exception\RuntimeException(
            new \Magento\Framework\Phrase('Any message')
        );

        $rssModel->expects($this->once())->method('createRssXml')->will(
            $this->throwException($exceptionMock)
        );

        $this->response->expects($this->once())->method('setHeader')->will($this->returnSelf());
        $this->rssFactory->expects($this->once())->method('create')->will($this->returnValue($rssModel));
        $this->rssManager->expects($this->once())->method('getProvider')->will($this->returnValue($dataProvider));

        $this->setExpectedException(\Magento\Framework\Exception\RuntimeException::class);
        $this->controller->execute();
    }
}
