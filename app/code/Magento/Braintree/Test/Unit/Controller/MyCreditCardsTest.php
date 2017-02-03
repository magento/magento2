<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Exception\LocalizedException;

class MyCreditCardsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var  \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var  \Magento\Framework\Controller\Result\Redirect
     */
    private $resultRedirect;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;


    /**
     * test setup
     */
    public function setUp()
    {
        $this->customerSession = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder('\Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder('\Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerUrl = $this->getMockBuilder('\Magento\Customer\Model\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionFlag = $this->getMockBuilder('\Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();


    }

    /**
     * Executes the dispatch override
     */
    public function testDispatchNoVault()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        $this->customerSession->expects($this->once())
            ->method('authenticate')
            ->willReturn(true);

        $this->config->expects($this->once())
            ->method('useVault')
            ->willReturn(false);
        /**
         * @var \Magento\Framework\App\RequestInterface $requestInterface
         */
        $requestInterface= $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->getMock();

        $notification = $objectManager->getObject(
            'Magento\Braintree\Test\Unit\Controller\Stub\MyCreditCardsStub',
            [
                'customerSession' => $this->customerSession,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'config' => $this->config,
                'customerUrl' => $this->customerUrl,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->dispatch($requestInterface));
    }

    /**
     * Executes the dispatch override
     */
    public function testDispatchNotLoggedIn()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->resultRedirect->expects($this->any())
            ->method('setPath')
            ->willReturnSelf();

        $this->customerSession->expects($this->any())
            ->method('authenticate')
            ->willReturn(false);

        $this->config->expects($this->any())
            ->method('useVault')
            ->willReturn(true);
        /**
         * @var \Magento\Framework\App\RequestInterface $requestInterface
         */
        $requestInterface= $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();


        $notification = $objectManager->getObject(
            'Magento\Braintree\Test\Unit\Controller\Stub\MyCreditCardsStub',
            [
                'customerSession' => $this->customerSession,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'config' => $this->config,
                'customerUrl' => $this->customerUrl,
                'response' => $this->_response,
            ]
        );

        $this->assertSame($this->_response, $notification->dispatch($requestInterface));
    }
}
