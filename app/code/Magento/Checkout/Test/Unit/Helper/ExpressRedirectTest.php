<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Helper;

class ExpressRedirectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_actionFlag;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManager;

    /**
     * Customer session
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_customerSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_context;

    /**
     * @var \Magento\Checkout\Helper\ExpressRedirect
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_actionFlag = $this->getMockBuilder(
            \Magento\Framework\App\ActionFlag::class
        )->disableOriginalConstructor()->setMethods(
            ['set']
        )->getMock();

        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->_customerSession = $this->getMockBuilder(
            \Magento\Customer\Model\Session::class
        )->disableOriginalConstructor()->setMethods(
            ['setBeforeAuthUrl']
        )->getMock();

        $this->_context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_helper = new \Magento\Checkout\Helper\ExpressRedirect(
            $this->_actionFlag,
            $this->_objectManager,
            $this->_customerSession,
            $this->_context
        );
    }

    /**
     * @dataProvider redirectLoginDataProvider
     * @param array $actionFlagList
     * @param string|null $customerBeforeAuthUrl
     * @param string|null $customerBeforeAuthUrlDefault
     */
    public function testRedirectLogin($actionFlagList, $customerBeforeAuthUrl, $customerBeforeAuthUrlDefault)
    {
        $expressRedirectMock = $this->getMockBuilder(
            \Magento\Checkout\Controller\Express\RedirectLoginInterface::class
        )->disableOriginalConstructor()->setMethods(
            [
                'getActionFlagList',
                'getResponse',
                'getCustomerBeforeAuthUrl',
                'getLoginUrl',
                'getRedirectActionName',
            ]
        )->getMock();
        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getActionFlagList'
        )->willReturn(
            $actionFlagList
        );

        $atIndex = 0;
        $actionFlagList = array_merge(['no-dispatch' => true], $actionFlagList);
        foreach ($actionFlagList as $actionKey => $actionFlag) {
            $this->_actionFlag->expects($this->at($atIndex))->method('set')->with('', $actionKey, $actionFlag);
            $atIndex++;
        }

        $expectedLoginUrl = 'loginURL';
        $expressRedirectMock->expects(
            $this->once()
        )->method(
            'getLoginUrl'
        )->willReturn(
            $expectedLoginUrl
        );

        $urlMock = $this->getMockBuilder(
            \Magento\Framework\Url\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['addRequestParam']
        )->getMock();
        $urlMock->expects(
            $this->once()
        )->method(
            'addRequestParam'
        )->with(
            $expectedLoginUrl,
            ['context' => 'checkout']
        )->willReturn(
            $expectedLoginUrl
        );

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            \Magento\Framework\Url\Helper\Data::class
        )->willReturn(
            $urlMock
        );

        $responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->setMethods(
            ['setRedirect', '__wakeup']
        )->getMock();
        $responseMock->expects($this->once())->method('setRedirect')->with($expectedLoginUrl);

        $expressRedirectMock->expects($this->once())->method('getResponse')->willReturn($responseMock);

        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getCustomerBeforeAuthUrl'
        )->willReturn(
            $customerBeforeAuthUrl
        );
        $expectedCustomerBeforeAuthUrl = $customerBeforeAuthUrl !== null
        ? $customerBeforeAuthUrl : $customerBeforeAuthUrlDefault;
        if ($expectedCustomerBeforeAuthUrl) {
            $this->_customerSession->expects(
                $this->once()
            )->method(
                'setBeforeAuthUrl'
            )->with(
                $expectedCustomerBeforeAuthUrl
            );
        }
        $this->_helper->redirectLogin($expressRedirectMock, $customerBeforeAuthUrlDefault);
    }

    /**
     * Data provider
     * @return array
     */
    public function redirectLoginDataProvider()
    {
        return [
            [[], 'beforeCustomerUrl', 'beforeCustomerUrlDEFAULT'],
            [['actionKey' => true], null, 'beforeCustomerUrlDEFAULT'],
            [[], 'beforeCustomerUrl', null]
        ];
    }
}
