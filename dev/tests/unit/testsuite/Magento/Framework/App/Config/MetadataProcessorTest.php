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
namespace Magento\Framework\App\Config;

class MetadataProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\MetadataProcessor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_initialConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modelPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendModelMock;

    protected function setUp()
    {
        $this->_modelPoolMock = $this->getMock(
            'Magento\Framework\App\Config\Data\ProcessorFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_initialConfigMock = $this->getMock('Magento\Framework\App\Config\Initial', array(), array(), '', false);
        $this->_backendModelMock = $this->getMock('Magento\Framework\App\Config\Data\ProcessorInterface');
        $this->_initialConfigMock->expects(
            $this->any()
        )->method(
            'getMetadata'
        )->will(
            $this->returnValue(array('some/config/path' => array('backendModel' => 'Custom_Backend_Model')))
        );
        $this->_model = new \Magento\Framework\App\Config\MetadataProcessor(
            $this->_modelPoolMock,
            $this->_initialConfigMock
        );
    }

    public function testProcess()
    {
        $this->_modelPoolMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Custom_Backend_Model'
        )->will(
            $this->returnValue($this->_backendModelMock)
        );
        $this->_backendModelMock->expects(
            $this->once()
        )->method(
            'processValue'
        )->with(
            'value'
        )->will(
            $this->returnValue('processed_value')
        );
        $data = array('some' => array('config' => array('path' => 'value')), 'active' => 1);
        $expectedResult = $data;
        $expectedResult['some']['config']['path'] = 'processed_value';
        $this->assertEquals($expectedResult, $this->_model->process($data));
    }
}
