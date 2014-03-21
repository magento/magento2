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
namespace Magento\Core\App\FrontController\Plugin;

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
     * @var \Magento\Stdlib\Cookie|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMock;

    /**
     * Request mock
     *
     * @var \Magento\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\View\Element\Messages|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\App\FrontController
     */
    protected $objectMock;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $responseMock;

    /**
     * Create cookie and request mock, version instance
     */
    public function setUp()
    {
        $this->cookieMock = $this->getMock('Magento\Stdlib\Cookie', array('set', 'get'), array(), '', false);
        $this->requestMock = $this->getMock('Magento\App\Request\Http', array('isPost'), array(), '', false);
        $this->configMock = $this->getMock('Magento\PageCache\Model\Config', array('isEnabled'), array(), '', false);
        $this->messageManagerMock = $this->getMockBuilder('Magento\Message\ManagerInterface')
            ->setMethods(array('getMessages', 'getCount'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->msgBox = new MessageBox(
            $this->cookieMock,
            $this->requestMock,
            $this->configMock,
            $this->messageManagerMock
        );

        $this->objectMock = $this->getMock('Magento\App\FrontController', array(), array(), '', false);
        $this->responseMock = $this->getMock('Magento\App\ResponseInterface', array(), array(), '', false);
    }

    /**
     * Handle private content message box cookie
     * Set cookie if it is not set.
     * Set or unset cookie on post request
     * In all other cases do nothing.
     */
    public function testAfterDispatch()
    {
        $messageCollectionMock = $this->getMock('Magento\Message\Collection', array('getCount'), array(), '', false);
        $messageCollectionMock->expects($this->once())
            ->method('getCount')
            ->will($this->returnValue(5));
        $this->messageManagerMock->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue($messageCollectionMock));
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->cookieMock->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo(\Magento\Core\App\FrontController\Plugin\MessageBox::COOKIE_NAME),
                1,
                $this->equalTo(\Magento\Core\App\FrontController\Plugin\MessageBox::COOKIE_PERIOD)
            );
        $this->assertInstanceOf(
            '\Magento\App\ResponseInterface',
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
            '\Magento\App\ResponseInterface',
            $this->msgBox->afterDispatch($this->objectMock, $this->responseMock)
        );
    }
}
