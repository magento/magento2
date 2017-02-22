<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Controller\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Contact\Controller\Index\Index|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_controller;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_view;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_redirect;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    public function setUp()
    {
        $this->_scopeConfig = $this->getMockForAbstractClass(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            ['isSetFlag'],
            '',
            false
        );
        $context = $this->getMock(
            '\Magento\Framework\App\Action\Context',
            ['getRequest', 'getResponse', 'getView', 'getUrl', 'getRedirect', 'getMessageManager'],
            [],
            '',
            false
        );
        $this->_url = $this->getMock('\Magento\Framework\UrlInterface', [], [], '', false);
        $this->_messageManager = $this->getMock('\Magento\Framework\Message\ManagerInterface', [], [], '', false);
        $this->_request = $this->getMock('\Magento\Framework\App\Request\Http', ['getPostValue'], [], '', false);
        $this->_redirect = $this->getMock('\Magento\Framework\App\Response\RedirectInterface', [], [], '', false);
        $this->_view = $this->getMock('\Magento\Framework\App\ViewInterface', [], [], '', false);
        $this->_storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $this->_transportBuilder = $this->getMock(
            '\Magento\Framework\Mail\Template\TransportBuilder',
            [],
            [],
            '',
            false
        );
        $this->_inlineTranslation = $this->getMock(
            '\Magento\Framework\Translate\Inline\StateInterface',
            [],
            [],
            '',
            false
        );
        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->_request));

        $context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(
                $this->getMock('\Magento\Framework\App\ResponseInterface', [], [], '', false)
            ));

        $context->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->_messageManager));

        $context->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($this->_url));

        $context->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($this->_redirect));

        $context->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->_view));

        $this->_controller = new \Magento\Contact\Controller\Index\Post(
            $context,
            $this->_transportBuilder,
            $this->_inlineTranslation,
            $this->_scopeConfig,
            $this->_storeManager
        );
    }

    public function testExecuteEmptyPost()
    {
        $this->_request->expects($this->once())->method('getPostValue')->will($this->returnValue([]));
        $this->_redirect->expects($this->once())->method('redirect');
        $this->_controller->execute();
    }

    /**
     * @dataProvider testPostDataProvider
     */
    public function testExecutePostValidation($postData, $exceptionExpected)
    {
        $this->_request->expects($this->any())
            ->method('getPostValue')
            ->will($this->returnValue($postData));

        if ($exceptionExpected) {
            $this->_messageManager->expects($this->once())
                ->method('addError');
        }
        $this->_inlineTranslation->expects($this->once())
            ->method('resume');

        $this->_inlineTranslation->expects($this->once())
            ->method('suspend');

        $this->_controller->execute();
    }

    public function testPostDataProvider()
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

        $this->_request->expects($this->any())
            ->method('getPostValue')
            ->will($this->returnValue($post));

        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface', [], [], '', false);

        $this->_transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->will($this->returnSelf());

        $this->_transportBuilder->expects($this->once())
            ->method('setTemplateOptions')
            ->with([
                'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->will($this->returnSelf());

        $this->_transportBuilder->expects($this->once())
            ->method('setTemplateVars')
            ->will($this->returnSelf());

        $this->_transportBuilder->expects($this->once())
            ->method('setFrom')
            ->will($this->returnSelf());

        $this->_transportBuilder->expects($this->once())
            ->method('addTo')
            ->will($this->returnSelf());

        $this->_transportBuilder->expects($this->once())
            ->method('setReplyTo')
            ->with($post['email'])
            ->will($this->returnSelf());

        $this->_transportBuilder->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $transport->expects($this->once())
            ->method('sendMessage');

        $this->_inlineTranslation->expects($this->once())
            ->method('resume');

        $this->_inlineTranslation->expects($this->once())
            ->method('suspend');

        $this->_controller->execute();
    }
}
