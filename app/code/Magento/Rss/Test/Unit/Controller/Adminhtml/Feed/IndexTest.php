<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\Controller\Adminhtml\Feed;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class IndexTest
 * @package Magento\Rss\Controller\Feed
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Rss\Controller\Feed\Index
     */
    protected $controller;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

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
        $this->rssManager = $this->createPartialMock(\Magento\Rss\Model\RssManager::class, ['getProvider']);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->rssFactory = $this->createPartialMock(\Magento\Rss\Model\RssFactory::class, ['create']);

        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $request->expects($this->once())->method('getParam')->with('type')->will($this->returnValue('rss_feed'));

        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setHeader', 'setBody', 'sendResponse'])
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $controllerArguments = $objectManagerHelper->getConstructArguments(
            \Magento\Rss\Controller\Adminhtml\Feed\Index::class,
            [
                'rssManager' => $this->rssManager,
                'scopeConfig' => $this->scopeConfigInterface,
                'rssFactory' => $this->rssFactory,
                'request' => $request,
                'response' => $this->response
            ]
        );
        $objectManager = $controllerArguments['context']->getObjectManager();
        $urlInterface = $this->createMock(\Magento\Backend\Model\UrlInterface::class);
        $objectManager->expects($this->at(0))->method('get')->with(\Magento\Backend\Model\UrlInterface::class)
            ->will($this->returnValue($urlInterface));
        $this->controller = $objectManagerHelper->getObject(
            \Magento\Rss\Controller\Adminhtml\Feed\Index::class,
            $controllerArguments
        );
    }

    public function testExecute()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->will($this->returnValue(true));
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        $rssModel = $this->createPartialMock(\Magento\Rss\Model\Rss::class, ['setDataProvider', 'createRssXml']);
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
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        $rssModel = $this->createPartialMock(\Magento\Rss\Model\Rss::class, ['setDataProvider']);
        $rssModel->expects($this->once())->method('setDataProvider')->will($this->returnSelf());

        $this->response->expects($this->once())->method('setHeader')->will($this->returnSelf());
        $this->rssFactory->expects($this->once())->method('create')->will($this->returnValue($rssModel));
        $this->rssManager->expects($this->once())->method('getProvider')->will($this->returnValue($dataProvider));

        $this->expectException('\Zend_Feed_Builder_Exception');
        $this->controller->execute();
    }
}
