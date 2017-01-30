<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\Creditcard;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Exception\LocalizedException;

class NewCardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    private $resultPage;

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
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * test setup
     */
    public function setUp()
    {
        $this->customerSession = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder('\Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->setMethods(['set', 'getTitle'])
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
    }

    /**
     * Executes the controller action and asserts non exception logic
     */
    public function testExecute()
    {
        $objectManager = new ObjectManagerHelper($this);
        $customerId = 1;

        $this->customerSession->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\NewCard',
            [
                'resultPageFactory' => $this->resultPageFactory,
                'customerSession' => $this->customerSession
            ]
        );

        $this->assertSame($this->resultPage, $notification->execute());
    }
}
