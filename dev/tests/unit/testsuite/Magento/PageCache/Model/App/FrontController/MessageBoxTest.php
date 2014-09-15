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
     * @var \Magento\Framework\Stdlib\CookieManager|\PHPUnit_Framework_MockObject_MockObject
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
        $this->cookieManagerMock = $this->getMockBuilder('Magento\Framework\Stdlib\CookieManager')
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->objectMock = $this->getMock('Magento\Framework\App\FrontController', array(), array(), '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\ResponseInterface', array(), array(), '', false);
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
