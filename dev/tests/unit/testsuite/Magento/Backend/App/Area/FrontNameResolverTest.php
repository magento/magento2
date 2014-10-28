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
        $this->_configMock = $this->getMock('\Magento\Backend\App\Config', array(), array(), '', false);
        $this->_model = new \Magento\Backend\App\Area\FrontNameResolver($this->_configMock, $this->_defaultFrontName);
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
