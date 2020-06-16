<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\TestCase;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ControllerAbstractTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected $_bootstrap;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Message\Manager */
    private $messageManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject | InterpretationStrategyInterface */
    private $interpretationStrategyMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | CookieManagerInterface */
    private $cookieManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Serialize\Serializer\Json
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $testObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())->method('unserialize')->willReturnCallback(
            function ($serializedData) {
                return json_decode($serializedData, true);
            }
        );
        $this->interpretationStrategyMock = $this->getMockForAbstractClass(InterpretationStrategyInterface::class);
        $this->interpretationStrategyMock->expects($this->any())
            ->method('interpret')
            ->willReturnCallback(
                function (MessageInterface $message) {
                    return $message->getText();
                }
            );

        $request = $testObjectManager->getObject(\Magento\TestFramework\Request::class);
        $response = $testObjectManager->getObject(\Magento\TestFramework\Response::class);
        $this->_objectManager =
            $this->createPartialMock(\Magento\TestFramework\ObjectManager::class, ['get', 'create']);
        $this->_objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                
                    [
                        [\Magento\Framework\App\RequestInterface::class, $request],
                        [\Magento\Framework\App\ResponseInterface::class, $response],
                        [\Magento\Framework\Message\Manager::class, $this->messageManager],
                        [CookieManagerInterface::class, $this->cookieManagerMock],
                        [\Magento\Framework\Serialize\Serializer\Json::class, $this->serializerMock],
                        [InterpretationStrategyInterface::class, $this->interpretationStrategyMock],
                    ]
                
            );
    }

    /**
     * Bootstrap instance getter.
     * Mocking real bootstrap
     *
     * @return \Magento\TestFramework\Bootstrap
     */
    protected function _getBootstrap()
    {
        if (!$this->_bootstrap) {
            $this->_bootstrap = $this->createPartialMock(\Magento\TestFramework\Bootstrap::class, ['getAllOptions']);
        }
        return $this->_bootstrap;
    }

    public function testGetRequest()
    {
        $request = $this->getRequest();
        $this->assertInstanceOf(\Magento\TestFramework\Request::class, $request);
    }

    public function testGetResponse()
    {
        $response = $this->getResponse();
        $this->assertInstanceOf(\Magento\TestFramework\Response::class, $response);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAssert404NotFound()
    {
        $this->getRequest()->setControllerName('noroute');
        $this->getResponse()->setBody(
            '404 Not Found test <h3>We are sorry, but the page you are looking for cannot be found.</h3>'
        );
        $this->assert404NotFound();

        $this->getResponse()->setBody('');
        try {
            $this->assert404NotFound();
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            return;
        }
        $this->fail('Failed response body validation');
    }

    /**
     */
    public function testAssertRedirectFailure()
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $this->assertRedirect();
    }

    /**
     * @depends testAssertRedirectFailure
     */
    public function testAssertRedirect()
    {
        /*
         * Prevent calling \Magento\Framework\App\Response\Http::setRedirect() because it dispatches event,
         * which requires fully initialized application environment intentionally not available
         * for unit tests
         */
        $setRedirectMethod = new \ReflectionMethod(\Magento\Framework\App\Response\Http::class, 'setRedirect');
        $setRedirectMethod->invoke($this->getResponse(), 'http://magentocommerce.com');
        $this->assertRedirect();
        $this->assertRedirect($this->equalTo('http://magentocommerce.com'));
    }

    /**
     * @param array $expectedMessages
     * @param string|null $messageTypeFilter
     * @dataProvider assertSessionMessagesDataProvider
     */
    public function testAssertSessionMessagesSuccess(array $expectedMessages, $messageTypeFilter)
    {
        $this->addSessionMessages();
        /** @var \PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\Constraint\Constraint $constraint */
        $constraint =
            $this->createPartialMock(\PHPUnit\Framework\Constraint\Constraint::class, ['toString', 'matches']);
        $constraint->expects(
            $this->once()
        )->method('matches')
            ->with($expectedMessages)
            ->willReturn(true);
        $this->assertSessionMessages($constraint, $messageTypeFilter);
    }

    public function assertSessionMessagesDataProvider()
    {
        return [
            'message warning type filtering' => [
                ['some_warning', 'warning_cookie'],
                MessageInterface::TYPE_WARNING,
            ],
            'message error type filtering' => [
                ['error_one', 'error_two', 'error_cookie'],
                MessageInterface::TYPE_ERROR,
            ],
            'message notice type filtering' => [
                ['some_notice', 'notice_cookie'],
                MessageInterface::TYPE_NOTICE,
            ],
            'message success type filtering'    => [
                ['success!', 'success_cookie'],
                MessageInterface::TYPE_SUCCESS,
            ],
        ];
    }

    public function testAssertSessionMessagesAll()
    {
        $this->addSessionMessages();

        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'some_warning',
                    'error_one',
                    'error_two',
                    'some_notice',
                    'success!',
                    'warning_cookie',
                    'notice_cookie',
                    'success_cookie',
                    'error_cookie',
                ]
            )
        );
    }

    public function testAssertSessionMessagesEmpty()
    {
        $messagesCollection =  new \Magento\Framework\Message\Collection();
        $this->messageManager->expects($this->any())->method('getMessages')
            ->willReturn($messagesCollection);

        $this->assertSessionMessages($this->isEmpty());
    }

    private function addSessionMessages()
    {
        // emulate session messages
        $messagesCollection = new \Magento\Framework\Message\Collection();
        $messagesCollection
            ->addMessage(new \Magento\Framework\Message\Warning('some_warning'))
            ->addMessage(new \Magento\Framework\Message\Error('error_one'))
            ->addMessage(new \Magento\Framework\Message\Error('error_two'))
            ->addMessage(new \Magento\Framework\Message\Notice('some_notice'))
            ->addMessage(new \Magento\Framework\Message\Success('success!'));
        $this->messageManager->expects($this->any())->method('getMessages')
            ->willReturn($messagesCollection);

        $cookieMessages = [
            [
                'type' => 'warning',
                'text' => 'warning_cookie',
            ],
            [
                'type' => 'notice',
                'text' => 'notice_cookie',
            ],
            [
                'type' => 'success',
                'text' => 'success_cookie',
            ],
            [
                'type' => 'error',
                'text' => 'error_cookie',
            ],
        ];

        $this->cookieManagerMock->expects($this->any())
            ->method('getCookie')
            ->willReturn(json_encode($cookieMessages));
    }
}
