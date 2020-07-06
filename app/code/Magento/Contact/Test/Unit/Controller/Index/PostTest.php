<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Controller\Index;

use Magento\Contact\Controller\Index\Post;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\HttpRequest;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Contact\Controller\Index\Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends TestCase
{
    /**
     * @var Post
     */
    private $controller;

    /**
     * @var RedirectFactory|MockObject
     */
    private $redirectResultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectResultMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $requestStub;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private $dataPersistorMock;

    /**
     * @var MailInterface|MockObject
     */
    private $mailMock;

    /**
     * test setup
     */
    protected function setUp(): void
    {
        $this->mailMock = $this->getMockBuilder(MailInterface::class)
            ->getMockForAbstractClass();
        $contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getResponse', 'getResultRedirectFactory', 'getUrl', 'getRedirect', 'getMessageManager']
        );
        $this->urlMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->requestStub = $this->createPartialMock(
            Http::class,
            ['getPostValue', 'getParams', 'getParam', 'isPost']
        );

        $this->redirectResultMock = $this->createMock(Redirect::class);
        $this->redirectResultMock->method('setPath')->willReturnSelf();

        $this->redirectResultFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->redirectResultFactoryMock
            ->method('create')
            ->willReturn($this->redirectResultMock);

        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->getMockForAbstractClass();

        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestStub);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->getMockForAbstractClass(ResponseInterface::class));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);
        $contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectResultFactoryMock);

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            Post::class,
            [
                'context' => $contextMock,
                'mail' => $this->mailMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    /**
     * Test ExecuteEmptyPost
     */
    public function testExecuteEmptyPost(): void
    {
        $this->stubRequestPostData([]);
        $this->assertSame($this->redirectResultMock, $this->controller->execute());
    }

    /**
     * Test exceute post validation
     * @param array $postData
     * @param bool $exceptionExpected
     * @dataProvider postDataProvider
     */
    public function testExecutePostValidation($postData, $exceptionExpected): void
    {
        $this->stubRequestPostData($postData);

        if ($exceptionExpected) {
            $this->messageManagerMock->expects($this->once())
                ->method('addErrorMessage');
            $this->dataPersistorMock->expects($this->once())
                ->method('set')
                ->with('contact_us', $postData);
        }

        $this->controller->execute();
    }

    /**
     * Data provider for test exceute post validation
     */
    public function postDataProvider(): array
    {
        return [
            [['name' => null, 'comment' => null, 'email' => '', 'hideit' => 'no'], true],
            [['name' => 'test', 'comment' => '', 'email' => '', 'hideit' => 'no'], true],
            [['name' => '', 'comment' => 'test', 'email' => '', 'hideit' => 'no'], true],
            [['name' => '', 'comment' => '', 'email' => 'test', 'hideit' => 'no'], true],
            [['name' => '', 'comment' => '', 'email' => '', 'hideit' => 'no'], true],
            [['name' => 'Name', 'comment' => 'Name', 'email' => 'invalidmail', 'hideit' => 'no'], true],
        ];
    }

    /**
     * Test ExecuteValidPost
     */
    public function testExecuteValidPost(): void
    {
        $postStub = [
            'name' => 'Name',
            'comment' => 'Comment',
            'email' => 'valid@mail.com',
            'hideit' => null
        ];

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('contact_us');

        $this->stubRequestPostData($postStub);

        $this->controller->execute();
    }

    /**
     * Stub request for post data
     *
     * @param array $post
     */
    private function stubRequestPostData($post): void
    {
        $this->requestStub
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(!empty($post));
        $this->requestStub->method('getPostValue')->willReturn($post);
        $this->requestStub->method('getParams')->willReturn($post);
        $this->requestStub->method('getParam')->willReturnCallback(
            function ($key) use ($post) {
                return $post[$key];
            }
        );
    }
}
