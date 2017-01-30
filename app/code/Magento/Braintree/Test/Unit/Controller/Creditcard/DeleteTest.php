<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\Creditcard;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Exception\LocalizedException;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    private $resultPage;

    /**
     * @var  \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var  \Magento\Framework\Controller\Result\Redirect
     */
    private $resultRedirect;

    /**
     * @var \Magento\Framework\View\Element\AbstractBlock
     */
    private $block;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\View\Layout
     */
    private $pageLayout;

    /**
     * @var \Magento\Braintree\Model\Vault
     */
    private $vault;

    /**
     * test setup
     */
    public function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->pageConfig = $this->getMockBuilder('\Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->setMethods(['set', 'getTitle'])
            ->getMock();

        $this->vault = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder('\Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactory = $this->getMockBuilder('\Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultPage = $this->getMockBuilder('\Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();


        $this->block = $this->getMockBuilder('\Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageLayout = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Executes the controller action and asserts non exception logic
     */
    public function testExecute()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->vault->expects($this->once())
            ->method('storedCard')
            ->willReturn(true);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn('token');


        $this->resultRedirectFactory->expects($this->never())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);
        $this->resultPage->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->pageLayout);
        $this->pageLayout->expects($this->any())
            ->method('getBlock')
            ->willReturn($this->block);
        $this->pageConfig->expects($this->once())
            ->method('getTitle')
            ->willReturnSelf();
        $this->resultPage->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfig);


        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\Delete',
            [
                'request' => $this->request,
                'resultPageFactory' => $this->resultPageFactory,
                'vault' => $this->vault,
            ]
        );

        $this->assertSame($this->resultPage, $notification->execute());
    }

    /**
     * Executes the controller action and asserts with redirect for non valid token param
     */
    public function testExecuteNoTokenRedirect()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->vault->expects($this->never())
            ->method('storedCard');

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn(null);

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->resultPageFactory->expects($this->never())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\Delete',
            [
                'request' => $this->request,
                'resultPageFactory' => $this->resultPageFactory,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'vault' => $this->vault,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->execute());
    }

    /**
     * Executes the controller action and asserts with redirects for non existing logic
     */
    public function testExecuteNonExistingTokenRedirect()
    {
        $objectManager = new ObjectManagerHelper($this);
        $phrase = new \Magento\Framework\Phrase('Credit card does not exist');
        $this->vault->expects($this->once())
            ->method('storedCard')
            ->willReturn(false);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn('token');

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->resultPageFactory->expects($this->never())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        /**
         * @var \Magento\Framework\Message\ManagerInterface $messageManager
         */
        $messageManager= $this->getMockBuilder('\Magento\Framework\Message\ManagerInterface')
            ->getMock();
        $messageManager->expects($this->once())
            ->method('addError')
            ->with($phrase);


        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\Delete',
            [
                'request' => $this->request,
                'resultPageFactory' => $this->resultPageFactory,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'vault' => $this->vault,
                'messageManager' => $messageManager,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->execute());
    }
}
