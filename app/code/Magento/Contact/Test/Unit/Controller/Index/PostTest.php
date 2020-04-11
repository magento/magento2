<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Controller\Index;

use Magento\Contact\Controller\Index\Index;
use Magento\Contact\Controller\Index\Post;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends TestCase
{
    /**
     * @var Index
     */
    private $controller;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

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
     * @var \Magento\Framework\App\Request\HttpRequest|MockObject
     */
    private $requestStub;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

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
        $this->mailMock = $this->getMockBuilder(MailInterface::class)->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getResponse', 'getResultRedirectFactory', 'getUrl', 'getRedirect', 'getMessageManager']
        );
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->messageManagerMock =
            $this->createMock(ManagerInterface::class);
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
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->getMockForAbstractClass();
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestStub);

        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->createMock(ResponseInterface::class));

        $context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $context->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);

        $context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectResultFactoryMock);

        $this->controller = new Post(
            $context,
            $this->configMock,
            $this->mailMock,
            $this->dataPersistorMock
        );
    }

    /**
     * testExecuteEmptyPost
     */
    public function testExecuteEmptyPost()
    {
        $this->stubRequestPostData([]);
        $this->assertSame($this->redirectResultMock, $this->controller->execute());
    }

    /**
     * @param array $postData
     * @param bool $exceptionExpected
     * @dataProvider postDataProvider
     */
    public function testExecutePostValidation($postData, $exceptionExpected)
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
     * @return array
     */
    public function postDataProvider()
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
     * testExecuteValidPost
     */
    public function testExecuteValidPost()
    {
        $post = ['name' => 'Name', 'comment' => 'Comment', 'email' => 'valid@mail.com', 'hideit' => null];

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('contact_us');

        $this->stubRequestPostData($post);

        $this->controller->execute();
    }

    /**
     * @param array $post
     */
    private function stubRequestPostData($post)
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
