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
            array('setIsUrlNotice'),
            array(),
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
            array('isFirstPageAfterLogin', 'processLogout', 'processLogin'),
            array(),
            '',
            false
        );
        $this->_authMock->expects($this->once())->method('getAuthStorage')->will($this->returnValue($authStorageMock));
        $authStorageMock->expects($this->once())->method('isFirstPageAfterLogin')->will($this->returnValue(true));
        $this->_sessionMock->expects($this->once())->method('setIsUrlNotice');
        $this->_model->create('fileName', null);
    }
}
