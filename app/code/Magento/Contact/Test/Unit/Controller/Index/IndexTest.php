<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller
     *
     * @var \Magento\Contact\Controller\Index\Index
     */
    protected $_controller;

    /**
     * Scope config mock
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * View mock
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * Url mock
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    protected function setUp()
    {
        $this->_scopeConfig = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['isSetFlag'],
            '',
            false
        );
        $context = $this->getMock(
            \Magento\Framework\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getResultFactory', 'getUrl'],
            [],
            '',
            false
        );

        $this->_url = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class, [], '', false);

        $context->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($this->_url));

        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue(
                $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false)
            ));

        $context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(
                $this->getMockForAbstractClass(\Magento\Framework\App\ResponseInterface::class, [], '', false)
            ));

        $this->resultFactory = $this->getMock(
            ResultFactory::class,
            [],
            [],
            '',
            false
        );

        $context->expects($this->once())
            ->method('getResultFactory')
            ->will($this->returnValue($this->resultFactory));

        $this->_controller = new \Magento\Contact\Controller\Index\Index(
            $context,
            $this->getMock(\Magento\Framework\Mail\Template\TransportBuilder::class, [], [], '', false),
            $this->getMockForAbstractClass(\Magento\Framework\Translate\Inline\StateInterface::class, [], '', false),
            $this->_scopeConfig,
            $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class, [], '', false)
        );
    }

    public function testExecute()
    {
        $resultStub = $this->getMockForAbstractClass(ResultInterface::class);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultStub);

        $this->assertSame($resultStub, $this->_controller->execute());
    }
}
