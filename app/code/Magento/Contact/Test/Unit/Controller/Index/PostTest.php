<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Controller\Index;
use Magento\Framework\Controller\Result\Redirect;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Contact\Controller\Index\Index
     */
    protected $controller;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectResultFactoryMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectResultMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Framework\App\Request\HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestStub;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilderMock;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inlineTranslationMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataPersistorMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['isSetFlag'],
            '',
            false
        );
        $context = $this->getMock(
            \Magento\Framework\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getResultRedirectFactory', 'getUrl', 'getRedirect', 'getMessageManager'],
            [],
            '',
            false
        );
        $this->urlMock = $this->getMock(\Magento\Framework\UrlInterface::class, [], [], '', false);
        $this->messageManagerMock =
            $this->getMock(\Magento\Framework\Message\ManagerInterface::class, [], [], '', false);
        $this->requestStub =
            $this->getMock(\Magento\Framework\App\Request\Http::class, ['getPostValue', 'getParams', 'getParam'], [], '', false);
        $this->redirectResultMock = $this->getMock(
            \Magento\Framework\Controller\Result\Redirect::class,
            [],
            [],
            '',
            false
        );
        $this->redirectResultMock->method('setPath')->willReturnSelf();
        $this->redirectResultFactoryMock = $this->getMock(\Magento\Framework\Controller\Result\RedirectFactory::class, ['create'], [], '', false);
        $this->redirectResultFactoryMock
            ->method('create')
            ->willReturn($this->redirectResultMock);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class, [], [], '', false);
        $this->transportBuilderMock = $this->getMock(
            \Magento\Framework\Mail\Template\TransportBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->inlineTranslationMock = $this->getMock(
            \Magento\Framework\Translate\Inline\StateInterface::class,
            [],
            [],
            '',
            false
        );
        $this->dataPersistorMock = $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)
            ->getMockForAbstractClass();
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestStub);

        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->getMock(\Magento\Framework\App\ResponseInterface::class, [], [], '', false));

        $context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $context->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);

        $context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectResultFactoryMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Contact\Controller\Index\Post::class,
            [
                'context' => $context,
                'transportBuilder' => $this->transportBuilderMock,
                'inlineTranslation' => $this->inlineTranslationMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->controller,
            'dataPersistor',
            $this->dataPersistorMock
        );
    }

    public function testExecuteEmptyPost()
    {
//        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn([]);
        $this->stubRequestPostData([]);
        $this->assertSame($this->redirectResultMock, $this->controller->execute());
    }

    /**
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
        $this->inlineTranslationMock->expects($this->once())
            ->method('resume');

        $this->inlineTranslationMock->expects($this->once())
            ->method('suspend');

        $this->controller->execute();
    }

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

    public function testExecuteValidPost()
    {
        $post = ['name' => 'Name', 'comment' => 'Comment', 'email' => 'valid@mail.com', 'hideit' => null];

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('contact_us');

        $this->stubRequestPostData($post);

        $transport = $this->getMock(\Magento\Framework\Mail\TransportInterface::class, [], [], '', false);

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with([
                'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setReplyTo')
            ->with($post['email'])
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage');

        $this->inlineTranslationMock->expects($this->once())
            ->method('resume');

        $this->inlineTranslationMock->expects($this->once())
            ->method('suspend');

        $this->controller->execute();
    }

    /**
     * @param array $post
     */
    private function stubRequestPostData($post)
    {
        $this->requestStub->method('getPostValue')->willReturn($post);
        $this->requestStub->method('getParams')->willReturn($post);
        $this->requestStub->method('getParam')->willReturnCallback(
            function ($key) use ($post) {
                return $post[$key];
            });
    }
}
