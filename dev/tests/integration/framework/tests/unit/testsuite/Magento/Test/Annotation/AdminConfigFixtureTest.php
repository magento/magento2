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

/**
 * Test class for \Magento\TestFramework\Annotation\AdminConfigFixture.
 */
namespace Magento\Test\Annotation;

class AdminConfigFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\AdminConfigFixture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->getMock(
            'Magento\TestFramework\Annotation\AdminConfigFixture',
            array('_getConfigValue', '_setConfigValue')
        );
    }

    /**
     * @magentoAdminConfigFixture any_config some_value
     */
    public function testConfig()
    {
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'any_config'
        )->will(
            $this->returnValue('some_value')
        );
        $this->_object->expects($this->at(1))->method('_setConfigValue')->with('any_config', 'some_value');
        $this->_object->startTest($this);

        $this->_object->expects($this->once())->method('_setConfigValue')->with('any_config', 'some_value');
        $this->_object->endTest($this);
    }

    public function testInitStoreAfterOfScope()
    {
        $this->_object->expects($this->never())->method('_getConfigValue');
        $this->_object->expects($this->never())->method('_setConfigValue');
        $this->_object->initStoreAfter();
    }

    /**
     * @magentoAdminConfigFixture any_config some_value
     */
    public function testInitStoreAfter()
    {
        $this->_object->startTest($this);
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'any_config'
        )->will(
            $this->returnValue('some_value')
        );
        $this->_object->expects($this->at(1))->method('_setConfigValue')->with('any_config', 'some_value');
        $this->_object->initStoreAfter();
    }
}
