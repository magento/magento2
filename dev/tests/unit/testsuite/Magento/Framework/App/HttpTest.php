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

namespace Magento\Framework\App;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\App\Http
     */
    protected $_http;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontControllerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_requestMock = $this->getMockBuilder(
            'Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->setMethods(['getFrontName'])->getMock();
        $frontName = 'frontName';
        $this->_requestMock->expects($this->once())->method('getFrontName')->will($this->returnValue($frontName));
        $areaCode = 'areaCode';
        $areaListMock = $this->getMockBuilder('Magento\Framework\App\AreaList')
            ->disableOriginalConstructor()
            ->setMethods(['getCodeByFrontName'])
            ->getMock();
        $areaListMock->expects($this->once())->method('getCodeByFrontName')->with($frontName)->will(
            $this->returnValue($areaCode)
        );
        $areaConfig = [];
        $configLoaderMock = $this->getMockBuilder(
            'Magento\Framework\App\ObjectManager\ConfigLoader'
        )->disableOriginalConstructor()->setMethods(['load'])->getMock();
        $configLoaderMock->expects($this->once())->method('load')->with($areaCode)->will(
            $this->returnValue($areaConfig)
        );
        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(['configure', 'get', 'create'])
            ->getMock();
        $objectManagerMock->expects($this->once())->method('configure')->with($areaConfig);
        $this->_responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods(['setHttpResponseCode', 'setBody', '__wakeup'])
            ->getMock();
        $this->_frontControllerMock = $this->getMockBuilder(
            'Magento\Framework\App\FrontControllerInterface'
        )->disableOriginalConstructor()->setMethods(['dispatch'])->getMock();
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\App\FrontControllerInterface')
            ->will($this->returnValue($this->_frontControllerMock));
        $this->_frontControllerMock->expects($this->once())->method('dispatch')->with($this->_requestMock)->will(
            $this->returnValue($this->_responseMock)
        );
        $this->_eventManagerMock = $this->getMockBuilder(
            'Magento\Framework\Event\Manager'
        )->disableOriginalConstructor()->setMethods(
            ['dispatch']
        )->getMock();
        $this->_filesystemMock = $this->getMockBuilder(
            'Magento\Framework\App\Filesystem'
        )->disableOriginalConstructor()->setMethods(
            ['getPath']
        )->getMock();

        $this->_http = $this->_objectManager->getObject(
            'Magento\Framework\App\Http',
            [
                'objectManager' => $objectManagerMock,
                'eventManager' => $this->_eventManagerMock,
                'areaList' => $areaListMock,
                'request' => $this->_requestMock,
                'response' => $this->_responseMock,
                'configLoader' => $configLoaderMock,
                'filesystem' => $this->_filesystemMock
            ]
        );
    }

    public function testLaunchSuccess()
    {
        $this->_eventManagerMock->expects($this->once())->method('dispatch')->with(
            'controller_front_send_response_before',
            array('request' => $this->_requestMock, 'response' => $this->_responseMock)
        );
        $this->assertSame($this->_responseMock, $this->_http->launch());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Message
     */
    public function testLaunchException()
    {
        $this->_frontControllerMock->expects($this->once())->method('dispatch')->with($this->_requestMock)->will(
            $this->returnCallback(
                function () {
                    throw new \Exception('Message');
                }
            )
        );
        $this->_http->launch();
    }
}
