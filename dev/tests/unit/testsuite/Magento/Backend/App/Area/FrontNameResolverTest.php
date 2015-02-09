<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Area;

class FrontNameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Area\FrontNameResolver
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var string
     */
    protected $_defaultFrontName = 'defaultFrontName';

    protected function setUp()
    {
        $deploymentConfigMock = $this->getMock('\Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(FrontNameResolver::PARAM_BACKEND_FRONT_NAME)
            ->will($this->returnValue($this->_defaultFrontName));
        $this->_configMock = $this->getMock('\Magento\Backend\App\Config', [], [], '', false);
        $this->_model = new \Magento\Backend\App\Area\FrontNameResolver($this->_configMock, $deploymentConfigMock);
    }

    public function testIfCustomPathUsed()
    {
        $this->_configMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            'admin/url/use_custom_path'
        )->will(
            $this->returnValue(true)
        );
        $this->_configMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            'admin/url/custom_path'
        )->will(
            $this->returnValue('expectedValue')
        );
        $this->assertEquals('expectedValue', $this->_model->getFrontName());
    }

    public function testIfCustomPathNotUsed()
    {
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'admin/url/use_custom_path'
        )->will(
            $this->returnValue(false)
        );
        $this->assertEquals($this->_defaultFrontName, $this->_model->getFrontName());
    }
}
