<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\App\FrontController;

use Magento\TestFramework\Helper\ObjectManager;

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
     * Cookie manager mock
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * Public cookie metadata mock
     *
     * @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $publicCookieMetadataMock;

    /**
     * Cookie metadata factory mock
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactoryMock;

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

    public function setUp()
    {
        $this->cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
        )->disableOriginalConstructor()
            ->getMock();
        $this->publicCookieMetadataMock = $this->getMockBuilder(
            'Magento\Framework\Stdlib\Cookie\PublicCookieMetadata'
        )->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->msgBox = (new ObjectManager($this))->getObject(
            'Magento\PageCache\Model\App\FrontController\MessageBox',
            [
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );

        $this->objectMock = $this->getMock('Magento\Framework\App\FrontController', [], [], '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);
    }

    /**
     * @param bool $isPost
     * @param int $numOfCalls
     * @dataProvider afterDispatchTestDataProvider
     */
    public function testAfterDispatch($isPost, $numOfCalls)
    {
        $this->messageManagerMock->expects($this->exactly($numOfCalls))
            ->method('hasMessages')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue($isPost));
        $this->cookieMetadataFactoryMock->expects($this->exactly($numOfCalls))
            ->method('createPublicCookieMetadata')
            ->will($this->returnValue($this->publicCookieMetadataMock));
        $this->publicCookieMetadataMock->expects(($this->exactly($numOfCalls)))
            ->method('setDuration')
            ->with(MessageBox::COOKIE_PERIOD)
            ->will($this->returnValue($this->publicCookieMetadataMock));
        $this->publicCookieMetadataMock->expects(($this->exactly($numOfCalls)))
            ->method('setPath')
            ->with('/')
            ->will($this->returnValue($this->publicCookieMetadataMock));
        $this->publicCookieMetadataMock->expects(($this->exactly($numOfCalls)))
            ->method('setHttpOnly')
            ->with(false)
            ->will($this->returnValue($this->publicCookieMetadataMock));
        $this->cookieManagerMock->expects($this->exactly($numOfCalls))
            ->method('setPublicCookie')
            ->with(
                MessageBox::COOKIE_NAME,
                1,
                $this->publicCookieMetadataMock
            );
        $this->assertSame($this->responseMock, $this->msgBox->afterDispatch($this->objectMock, $this->responseMock));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function afterDispatchTestDataProvider()
    {
        return [
            [true, 1],
            [false, 0],
        ];
    }
}
