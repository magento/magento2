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
    protected $actionFlag;

    /**
     * @var MockObject
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $customerSession;

    /**
     * @var MockObject
     */
    protected $context;

    /**
     * @var ExpressRedirect
     */
    protected $helper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->actionFlag = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set'])
            ->getMock();

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setBeforeAuthUrl'])->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new ExpressRedirect(
            $this->actionFlag,
            $this->objectManager,
            $this->customerSession,
            $this->context
        );
    }

    /**
     * @param array $actionFlagList
     * @param string|null $customerBeforeAuthUrl
     * @param string|null $customerBeforeAuthUrlDefault
     *
     * @return void
     * @dataProvider redirectLoginDataProvider
     */
    public function testRedirectLogin(
        array $actionFlagList,
        ?string $customerBeforeAuthUrl,
        ?string $customerBeforeAuthUrlDefault
    ): void {
        $expressRedirectMock = $this->getMockBuilder(RedirectLoginInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getActionFlagList',
                    'getResponse',
                    'getCustomerBeforeAuthUrl',
                    'getLoginUrl',
                    'getRedirectActionName'
                ]
            )->getMock();
        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getActionFlagList'
        )->willReturn(
            $actionFlagList
        );
        $actionFlagList = array_merge(['no-dispatch' => true], $actionFlagList);
        $withArgs = [];

        foreach ($actionFlagList as $actionKey => $actionFlag) {
            $withArgs[] = ['', $actionKey, $actionFlag];
        }
        $this->actionFlag
            ->method('set')
            ->withConsecutive(...$withArgs);

        $expectedLoginUrl = 'loginURL';
        $expressRedirectMock->expects(
            $this->once()
        )->method(
            'getLoginUrl'
        )->willReturn(
            $expectedLoginUrl
        );

        $urlMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addRequestParam'])->getMock();
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

        $this->objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            Data::class
        )->willReturn(
            $urlMock
        );

        $responseMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setRedirect'])->getMock();
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
            $this->customerSession->expects(
                $this->once()
            )->method(
                'setBeforeAuthUrl'
            )->with(
                $expectedCustomerBeforeAuthUrl
            );
        }
        $this->helper->redirectLogin($expressRedirectMock, $customerBeforeAuthUrlDefault);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function redirectLoginDataProvider(): array
    {
        return [
            [[], 'beforeCustomerUrl', 'beforeCustomerUrlDEFAULT'],
            [['actionKey' => true], null, 'beforeCustomerUrlDEFAULT'],
            [[], 'beforeCustomerUrl', null]
        ];
    }
}
