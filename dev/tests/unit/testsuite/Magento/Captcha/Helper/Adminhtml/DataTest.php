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
            array('getValue', 'setValue', 'isSetFlag')
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

        $filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', array(), array(), '', false);

        $filesystemMock->expects($this->any())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));
        $directoryMock->expects($this->any())->method('getAbsolutePath')->will($this->returnArgument(0));

        $this->_model = new \Magento\Captcha\Helper\Adminhtml\Data(
            $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false),
            $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false),
            $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
            $filesystemMock,
            $this->getMock('Magento\Captcha\Model\CaptchaFactory', array(), array(), '', false),
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
