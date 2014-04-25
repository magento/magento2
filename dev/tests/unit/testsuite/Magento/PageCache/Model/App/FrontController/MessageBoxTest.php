<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Model\App\FrontController;

/**
 * Class MessageBoxTest
 */
class MessageBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Version instance
     *
     * @var MessageBox
     */
    protected $msgBox;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Cookie mock
     *
     * @var \Magento\Framework\Stdlib\Cookie|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMock;

    /**
     * Request mock
     *
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\FrontController
     */
    protected $objectMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $responseMock;

    /**
     * Create cookie and request mock, version instance
     */
    public function setUp()
    {
        $this->cookieMock = $this->getMock('Magento\Framework\Stdlib\Cookie', array(), array(), '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', array('isPost'), array(), '', false);
        $this->configMock = $this->getMock('Magento\PageCache\Model\Config', array('isEnabled'), array(), '', false);
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->msgBox = new MessageBox(
            $this->cookieMock,
            $this->requestMock,
            $this->configMock,
            $this->messageManagerMock
        );

        $this->objectMock = $this->getMock('Magento\Framework\App\FrontController', array(), array(), '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\ResponseInterface', array(), array(), '', false);
    }

    /**
     * Handle private content message box cookie
     * Set cookie if it is not set.
     * Set or unset cookie on post request
     * In all other cases do nothing.
     */
    public function testAfterDispatch()
    {
        $this->messageManagerMock->expects($this->once())
            ->method('hasMessages')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->cookieMock->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo(MessageBox::COOKIE_NAME),
                1,
                $this->equalTo(MessageBox::COOKIE_PERIOD),
                '/'
            );
        $this->assertInstanceOf(
            '\Magento\Framework\App\ResponseInterface',
            $this->msgBox->afterDispatch($this->objectMock, $this->responseMock)
        );
    }

    /**
     * IF request is not POST
     */
    public function testProcessNoPost()
    {
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(false));
        $this->messageManagerMock->expects($this->never())
            ->method('getMessages');
        $this->assertInstanceOf(
            '\Magento\Framework\App\ResponseInterface',
            $this->msgBox->afterDispatch($this->objectMock, $this->responseMock)
        );
    }
}
