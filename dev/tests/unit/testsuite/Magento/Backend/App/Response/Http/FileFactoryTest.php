<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Response\Http;

class FileFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendUrl;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            ['setRedirect', '__wakeup'],
            [],
            '',
            false
        );
        $this->_responseMock->expects(
            $this->any()
        )->method(
            'setRedirect'
        )->will(
            $this->returnValue($this->_responseMock)
        );
        $this->_sessionMock = $this->getMock(
            'Magento\Backend\Model\Session',
            ['setIsUrlNotice'],
            [],
            '',
            false
        );
        $this->_backendUrl = $this->getMock('Magento\Backend\Model\Url', [], [], '', false);
        $this->_authMock = $this->getMock('Magento\Backend\Model\Auth', [], [], '', false);
        $this->_model = $helper->getObject(
            'Magento\Backend\App\Response\Http\FileFactory',
            [
                'response' => $this->_responseMock,
                'auth' => $this->_authMock,
                'backendUrl' => $this->_backendUrl,
                'session' => $this->_sessionMock
            ]
        );
    }

    public function testCreate()
    {
        $authStorageMock = $this->getMock(
            'Magento\Backend\Model\Auth\Session',
            ['isFirstPageAfterLogin', 'processLogout', 'processLogin'],
            [],
            '',
            false
        );
        $this->_authMock->expects($this->once())->method('getAuthStorage')->will($this->returnValue($authStorageMock));
        $authStorageMock->expects($this->once())->method('isFirstPageAfterLogin')->will($this->returnValue(true));
        $this->_sessionMock->expects($this->once())->method('setIsUrlNotice');
        $this->_model->create('fileName', null);
    }
}
