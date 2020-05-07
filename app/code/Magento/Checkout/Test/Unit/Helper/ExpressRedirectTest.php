<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Checkout\Controller\Express\RedirectLoginInterface;
use Magento\Checkout\Helper\ExpressRedirect;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Url\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExpressRedirectTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_actionFlag;

    /**
     * @var MockObject
     */
    protected $_objectManager;

    /**
     * Customer session
     *
     * @var MockObject
     */
    protected $_customerSession;

    /**
     * @var MockObject
     */
    protected $_context;

    /**
     * @var ExpressRedirect
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_actionFlag = $this->getMockBuilder(
            ActionFlag::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['set']
            )->getMock();

        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->_customerSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['setBeforeAuthUrl']
            )->getMock();

        $this->_context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_helper = new ExpressRedirect(
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
            RedirectLoginInterface::class
        )->disableOriginalConstructor()
            ->setMethods(
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
            Data::class
        )->disableOriginalConstructor()
            ->setMethods(
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
            Data::class
        )->willReturn(
            $urlMock
        );

        $responseMock = $this->getMockBuilder(
            Http::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['setRedirect']
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
