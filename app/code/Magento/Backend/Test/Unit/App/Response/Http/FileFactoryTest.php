<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App\Response\Http;

class FileFactoryTest extends \PHPUnit\Framework\TestCase
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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['setRedirect', '__wakeup']
        );
        $this->_responseMock->expects(
            $this->any()
        )->method(
            'setRedirect'
        )->will(
            $this->returnValue($this->_responseMock)
        );
        $this->_sessionMock = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice']);
        $this->_backendUrl = $this->createMock(\Magento\Backend\Model\Url::class);
        $this->_authMock = $this->createMock(\Magento\Backend\Model\Auth::class);
        $this->_model = $helper->getObject(
            \Magento\Backend\App\Response\Http\FileFactory::class,
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
        $authStorageMock = $this->createPartialMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['isFirstPageAfterLogin', 'processLogout', 'processLogin']
        );
        $this->_authMock->expects($this->once())->method('getAuthStorage')->will($this->returnValue($authStorageMock));
        $authStorageMock->expects($this->once())->method('isFirstPageAfterLogin')->will($this->returnValue(true));
        $this->_sessionMock->expects($this->once())->method('setIsUrlNotice');
        $this->_model->create('fileName', null);
    }
}
