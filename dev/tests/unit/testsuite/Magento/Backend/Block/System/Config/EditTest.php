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
namespace Magento\Backend\Block\System\Config;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\System\Config\Edit
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_systemConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionMock;

    protected function setUp()
    {
        $this->_systemConfigMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure',
            array(),
            array(),
            '',
            false,
            false
        );

        $this->_requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'section'
        )->will(
            $this->returnValue('test_section')
        );

        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false, false);

        $this->_urlModelMock = $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false, false);

        $this->_sectionMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Section',
            array(),
            array(),
            '',
            false
        );
        $this->_systemConfigMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->with(
            'test_section'
        )->will(
            $this->returnValue($this->_sectionMock)
        );

        $data = array(
            'data' => array('systemConfig' => $this->_systemConfigMock),
            'request' => $this->_requestMock,
            'layout' => $this->_layoutMock,
            'urlBuilder' => $this->_urlModelMock,
            'configStructure' => $this->_systemConfigMock
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_object = $helper->getObject('Magento\Backend\Block\System\Config\Edit', $data);
    }

    public function testGetSaveButtonHtml()
    {
        $expected = 'element_html_code';

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'getChildName'
        )->with(
            null,
            'save_button'
        )->will(
            $this->returnValue('test_child_name')
        );

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'renderElement'
        )->with(
            'test_child_name'
        )->will(
            $this->returnValue('element_html_code')
        );

        $this->assertEquals($expected, $this->_object->getSaveButtonHtml());
    }

    public function testGetSaveUrl()
    {
        $expectedUrl = '*/system_config/save';
        $expectedParams = array('_current' => true);

        $this->_urlModelMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $expectedUrl,
            $expectedParams
        )->will(
            $this->returnArgument(0)
        );

        $this->assertEquals($expectedUrl, $this->_object->getSaveUrl());
    }
}
