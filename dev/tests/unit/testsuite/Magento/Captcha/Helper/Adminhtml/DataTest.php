<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Helper\Adminhtml;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Captcha\Helper\Adminhtml\Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * setUp
     */
    protected function setUp()
    {
        $backendConfig = $this->getMockBuilder(
            'Magento\Backend\App\ConfigInterface'
        )->disableOriginalConstructor()->setMethods(
            ['getValue', 'setValue', 'isSetFlag']
        )->getMock();
        $backendConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            'admin/captcha/qwe'
        )->will(
            $this->returnValue('1')
        );

        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);

        $filesystemMock->expects($this->any())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));
        $directoryMock->expects($this->any())->method('getAbsolutePath')->will($this->returnArgument(0));

        $this->_model = new \Magento\Captcha\Helper\Adminhtml\Data(
            $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false),
            $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false),
            $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
            $filesystemMock,
            $this->getMock('Magento\Captcha\Model\CaptchaFactory', [], [], '', false),
            $backendConfig
        );
    }

    public function testGetConfig()
    {
        $this->assertEquals('1', $this->_model->getConfig('qwe'));
    }

    /**
     * @covers \Magento\Captcha\Helper\Adminhtml\Data::_getWebsiteCode
     */
    public function testGetWebsiteId()
    {
        $this->assertStringEndsWith('/admin/', $this->_model->getImgDir());
    }
}
