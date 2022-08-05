<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\App\Response\Redirect;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Controller\Index\Index;
use Magento\Wishlist\Controller\Index\Plugin;
use Magento\Wishlist\Model\AuthenticationState;
use Magento\Wishlist\Model\AuthenticationStateInterface;
use Magento\Wishlist\Model\DataSerializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for wishlist plugin before dispatch
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginTest extends TestCase
{
    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    /**
     * @var AuthenticationStateInterface|MockObject
     */
    protected $authenticationState;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $config;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirector;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var DataSerializer|MockObject
     */
    private $dataSerializer;

    /**
     * @var FormKey|MockObject
     */
    private $formKey;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'authenticate',
                    'getBeforeWishlistUrl',
                    'setBeforeWishlistUrl',
                    'setBeforeWishlistRequest',
                    'getBeforeWishlistRequest',
                    'setBeforeRequestParams',
                    'setBeforeModuleName',
                    'setBeforeControllerName',
                    'setBeforeAction',
                ]
            )->getMock();

        $this->authenticationState = $this->createMock(AuthenticationState::class);
        $this->config = $this->createMock(Config::class);
        $this->redirector = $this->createMock(Redirect::class);
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->request = $this->createMock(Http::class);
        $this->dataSerializer = $this->createMock(DataSerializer::class);
        $this->formKey = $this->createMock(FormKey::class);
        $this->formKeyValidator = $this->createMock(Validator::class);
    }

    /**
     * @return Plugin
     */
    protected function getPlugin()
    {
        return new Plugin(
            $this->customerSession,
            $this->authenticationState,
            $this->config,
            $this->redirector,
            $this->messageManager,
            $this->dataSerializer,
            $this->formKey,
            $this->formKeyValidator
        );
    }

    public function testBeforeDispatch()
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $refererUrl = 'http://referer-url.com';
        $params = [
            'product' => 1,
            'login' => [],
        ];

        $actionFlag = $this->createMock(ActionFlag::class);
        $indexController = $this->createMock(Index::class);

        $actionFlag
            ->expects($this->once())
            ->method('set')
            ->with('', 'no-dispatch', true)
            ->willReturn(true);

        $indexController
            ->expects($this->once())
            ->method('getActionFlag')
            ->willReturn($actionFlag);

        $this->authenticationState
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->redirector
            ->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn($params);

        $this->request
            ->expects($this->exactly(2))
            ->method('getActionName')
            ->willReturn('add');

        $this->customerSession->expects($this->once())
            ->method('authenticate')
            ->willReturn(false);
        $this->customerSession->expects($this->once())
            ->method('getBeforeWishlistUrl')
            ->willReturn(false);
        $this->customerSession->expects($this->once())
            ->method('setBeforeWishlistUrl')
            ->with($refererUrl)
            ->willReturnSelf();
        $this->customerSession->expects($this->once())
            ->method('setBeforeWishlistRequest')
            ->with(['product' => 1])
            ->willReturnSelf();
        $this->customerSession->expects($this->once())
            ->method('getBeforeWishlistRequest')
            ->willReturn($params);
        $this->customerSession->expects($this->once())
            ->method('setBeforeRequestParams')
            ->with($params)
            ->willReturnSelf();
        $this->customerSession->expects($this->once())
            ->method('setBeforeModuleName')
            ->with('wishlist')
            ->willReturnSelf();
        $this->customerSession->expects($this->once())
            ->method('setBeforeControllerName')
            ->with('index')
            ->willReturnSelf();
        $this->customerSession->expects($this->once())
            ->method('setBeforeAction')
            ->with('add')
            ->willReturnSelf();

        $this->config
            ->expects($this->once())
            ->method('isSetFlag')
            ->with('wishlist/general/active', ScopeInterface::SCOPE_STORES)
            ->willReturn(false);

        $this->getPlugin()->beforeDispatch($indexController, $this->request);
    }
}
